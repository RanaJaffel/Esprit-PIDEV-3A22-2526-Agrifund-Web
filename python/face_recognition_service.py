#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Service de reconnaissance faciale pour AgriFund
Utilise OpenCV avec des modèles de deep learning
"""

import sys
import io
import json
import os
import base64
from datetime import datetime

# ✅ FIX 1: Force UTF-8 output on Windows BEFORE any print/import
if sys.platform == 'win32':
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')
    sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8', errors='replace')

# ✅ FIX 2: Suppress noisy library warnings
import warnings
warnings.filterwarnings('ignore')

os.environ['TF_CPP_MIN_LOG_LEVEL'] = '3'

import cv2
import numpy as np
from PIL import Image
import mysql.connector

def convert_numpy(obj):
    """Convert numpy types to native Python types for JSON"""
    if isinstance(obj, np.bool_):
        return bool(obj)

    if isinstance(obj, (np.integer,)):
        return int(obj)

    if isinstance(obj, (np.floating,)):
        return float(obj)

    if isinstance(obj, np.ndarray):
        return obj.tolist()

    return obj
def safe_output(result: dict):
    """Guaranteed clean JSON output to stdout"""
    try:
        output = json.dumps(
            result,
            default=convert_numpy,
            ensure_ascii=True
        )
    except Exception as e:
        output = json.dumps({
            'success': False,
            'error': f'JSON serialization error: {str(e)}',
            'code': 'JSON_ERROR'
        }, ensure_ascii=True)

    if hasattr(sys.stdout, 'buffer'):
        sys.stdout.buffer.write(output.encode('utf-8'))
        sys.stdout.buffer.write(b'\n')
        sys.stdout.buffer.flush()
    else:
        print(output)


def log_debug(message: str):
    """✅ All debug output goes to stderr"""
    try:
        print(f"[DEBUG] {message}", file=sys.stderr)
    except Exception:
        pass


class FaceRecognitionService:
    def __init__(self):
        self.db_config = {
            'host': '127.0.0.1',
            'user': 'root',
            'password': '',
            'database': 'agrifund',
            'charset': 'utf8mb4',
            'collation': 'utf8mb4_unicode_ci'
        }
        
        # ✅ Paramètres OpenCV
        self.tolerance = 0.6  # Seuil de similarité (cosine distance)
        self.quality_threshold = 100  # Taille minimale du visage
        
        # ✅ Chemins des modèles OpenCV (à télécharger)
        # Téléchargez ces fichiers depuis le dépôt officiel OpenCV
        model_dir = os.path.join(os.path.dirname(__file__), 'models')
        
        # Détection de visage (Haar Cascade ou DNN)
        self.face_cascade_path = os.path.join(model_dir, 'haarcascade_frontalface_default.xml')
        
        # Reconnaissance faciale (FaceNet ou OpenFace)
        self.face_model_path = os.path.join(model_dir, 'openface.nn4.small2.v1.t7')

        
        # Initialiser le détecteur de visage
        if os.path.exists(self.face_cascade_path):
            self.face_cascade = cv2.CascadeClassifier(self.face_cascade_path)
        else:
            log_debug(f"WARNING: Haar cascade not found at {self.face_cascade_path}")
            self.face_cascade = None
        
        # Initialiser le modèle de reconnaissance (DNN)
        if os.path.exists(self.face_model_path):
            self.face_net = cv2.dnn.readNetFromTorch(self.face_model_path)
        else:
            log_debug(f"WARNING: Face model not found at {self.face_model_path}")
            self.face_net = None

    def get_db_connection(self):
        """Établir une connexion à la base de données"""
        try:
            conn = mysql.connector.connect(**self.db_config)
            return conn
        except mysql.connector.Error as err:
            log_debug(f"DB connection error: {err}")
            return None

    def load_image_from_base64(self, base64_string):
        """Charger une image depuis une chaîne base64"""
        try:
            if ',' in base64_string:
                base64_string = base64_string.split(',')[1]
            
            base64_string = base64_string.strip()
            image_data = base64.b64decode(base64_string)
            image = Image.open(io.BytesIO(image_data))
            
            if image.mode != 'RGB':
                image = image.convert('RGB')
            
            return np.array(image)
        except Exception as e:
            raise Exception(f"Erreur de chargement de l'image base64: {str(e)}")

    def load_image_from_file(self, file_path):
        """Charger une image depuis un fichier"""
        try:
            file_path = os.path.normpath(file_path)
            log_debug(f"Loading image from: {file_path}")
            
            if not os.path.exists(file_path):
                raise Exception(f"Fichier introuvable: {file_path}")
            
            file_size = os.path.getsize(file_path)
            log_debug(f"File size: {file_size} bytes")
            
            if file_size < 100:
                raise Exception(f"Fichier trop petit: {file_size} bytes")
            
            # Charger avec OpenCV (BGR)
            image = cv2.imread(file_path)
            
            if image is None:
                raise Exception("Impossible de lire l'image avec OpenCV")
            
            # Convertir BGR -> RGB
            image = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
            
            log_debug(f"Image loaded: shape={image.shape}, dtype={image.dtype}")
            return image
            
        except Exception as e:
            raise Exception(f"Erreur de chargement du fichier: {str(e)}")

    def detect_faces(self, image):
        """Détecter les visages dans une image avec OpenCV"""
        if self.face_cascade is None:
            raise Exception("Haar Cascade non initialisé")
        
        # Convertir en niveaux de gris pour la détection
        gray = cv2.cvtColor(image, cv2.COLOR_RGB2GRAY)
        
        # Détecter les visages
        faces = self.face_cascade.detectMultiScale(
            gray,
            scaleFactor=1.1,
            minNeighbors=5,
            minSize=(100, 100),
            flags=cv2.CASCADE_SCALE_IMAGE
        )
        
        return faces

    def extract_face_embedding(self, image, face_location):
        """Extraire l'embedding d'un visage avec OpenCV DNN"""
        if self.face_net is None:
            raise Exception("Modèle de reconnaissance non initialisé")
        
        x, y, w, h = face_location
        
        # Extraire la région du visage
        face_roi = image[y:y+h, x:x+w]
        
        # Prétraitement pour le modèle (96x96 pour OpenFace)
        face_blob = cv2.dnn.blobFromImage(
            face_roi,
            1.0 / 255,
            (96, 96),
            (0, 0, 0),
            swapRB=True,
            crop=False
        )
        
        # Passer par le réseau
        self.face_net.setInput(face_blob)
        embedding = self.face_net.forward()
        
        # Retourner le vecteur d'embedding (128 dimensions pour OpenFace)
        return embedding.flatten()

    def detect_and_validate_face(self, image):
        """Détecter un visage et valider sa qualité"""
        log_debug("Detecting faces...")
        
        faces = self.detect_faces(image)
        
        log_debug(f"Found {len(faces)} face(s)")
        
        if len(faces) == 0:
            return {
                'success': False,
                'error': 'Aucun visage detecte. Placez votre visage face a la camera.',
                'code': 'NO_FACE'
            }
        
        if len(faces) > 1:
            return {
                'success': False,
                'error': 'Plusieurs visages detectes. Assurez-vous d\'etre seul(e) dans le cadre.',
                'code': 'MULTIPLE_FACES'
            }
        
        x, y, w, h = faces[0]
        
        log_debug(f"Face size: {w}x{h}")
        
        if w < self.quality_threshold or h < self.quality_threshold:
            return {
                'success': False,
                'error': 'Visage trop petit ou eloigne. Rapprochez-vous de la camera.',
                'code': 'FACE_TOO_SMALL'
            }
        
        return {
            'success': True,
            'location': faces[0]
        }

    def cosine_distance(self, embedding1, embedding2):
        """Calculer la distance cosinus entre deux embeddings"""
        # Normaliser les vecteurs
        embedding1 = embedding1 / np.linalg.norm(embedding1)
        embedding2 = embedding2 / np.linalg.norm(embedding2)
        
        # Distance cosinus = 1 - similarité cosinus
        return 1.0 - np.dot(embedding1, embedding2)

    def enroll_face(self, image_source, user_id, source_type='file'):
        """Enregistrer un visage pour un utilisateur"""
        try:
            log_debug(f"=== ENROLL START === user_id={user_id}, source_type={source_type}")
            
            # Charger l'image
            if source_type == 'base64':
                image = self.load_image_from_base64(image_source)
            else:
                image = self.load_image_from_file(image_source)
            
            log_debug(f"Image loaded successfully: {image.shape}")
            
            # Détecter et valider le visage
            validation = self.detect_and_validate_face(image)
            if not validation['success']:
                return validation
            
            # Extraire l'embedding
            log_debug("Extracting face embedding...")
            face_location = validation['location']
            face_embedding = self.extract_face_embedding(image, face_location)
            
            log_debug(f"Face embedding extracted: {len(face_embedding)} dimensions")
            
            # Sauvegarder dans la base de données
            conn = self.get_db_connection()
            if not conn:
                return {
                    'success': False,
                    'error': 'Erreur de connexion a la base de donnees',
                    'code': 'DB_ERROR'
                }
            
            cursor = None
            try:
                cursor = conn.cursor()
                encoding_json = json.dumps(face_embedding.tolist())
                now = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
                
                query = """
                    UPDATE utilisateur 
                    SET face_descriptor = %s, 
                        face_enabled = 1,
                        face_enrolled_at = %s
                    WHERE id = %s
                """
                
                cursor.execute(query, (encoding_json, now, user_id))
                conn.commit()
                
                affected = cursor.rowcount
                log_debug(f"DB updated: {affected} row(s) affected")
                
                if affected == 0:
                    return {
                        'success': False,
                        'error': f'Utilisateur {user_id} introuvable dans la base de donnees',
                        'code': 'USER_NOT_FOUND'
                    }
                
                return {
                    'success': True,
                    'message': 'Visage enregistre avec succes',
                    'enrolled_at': now,
                    'encoding_size': len(face_embedding)
                }
                
            except mysql.connector.Error as err:
                return {
                    'success': False,
                    'error': f'Erreur base de donnees: {str(err)}',
                    'code': 'DB_ERROR'
                }
            finally:
                if cursor:
                    cursor.close()
                conn.close()
                
        except Exception as e:
            log_debug(f"EXCEPTION: {str(e)}")
            return {
                'success': False,
                'error': str(e),
                'code': 'UNKNOWN_ERROR'
            }

    def verify_face(self, image_source, user_id, source_type='file', log_ip=None, log_user_agent=None):
        """Vérifier un visage contre celui enregistré"""
        try:
            log_debug(f"=== VERIFY START === user_id={user_id}, source_type={source_type}")
            
            # Charger l'image
            if source_type == 'base64':
                image = self.load_image_from_base64(image_source)
            else:
                image = self.load_image_from_file(image_source)
            
            # Détecter et valider le visage
            validation = self.detect_and_validate_face(image)
            if not validation['success']:
                self.log_attempt(user_id, False, 0.0, log_ip, log_user_agent)
                return {**validation, 'match': False}
            
            # Extraire l'embedding
            face_location = validation['location']
            unknown_embedding = self.extract_face_embedding(image, face_location)
            
            # Récupérer le visage enregistré
            conn = self.get_db_connection()
            if not conn:
                return {
                    'success': False,
                    'match': False,
                    'error': 'Erreur de connexion a la base de donnees',
                    'code': 'DB_ERROR'
                }
            
            cursor = None
            try:
                cursor = conn.cursor(dictionary=True)
                cursor.execute(
                    "SELECT face_descriptor, face_enabled FROM utilisateur WHERE id = %s",
                    (user_id,)
                )
                result = cursor.fetchone()
                
                if not result:
                    return {
                        'success': False,
                        'match': False,
                        'error': 'Utilisateur introuvable',
                        'code': 'USER_NOT_FOUND'
                    }
                
                if not result['face_enabled']:
                    return {
                        'success': False,
                        'match': False,
                        'error': 'Reconnaissance faciale desactivee',
                        'code': 'FACE_DISABLED'
                    }
                
                if not result['face_descriptor']:
                    return {
                        'success': False,
                        'match': False,
                        'error': 'Aucun visage enregistre pour cet utilisateur',
                        'code': 'NO_FACE_ENROLLED'
                    }
                
                # Comparer les visages
                known_embedding = np.array(json.loads(result['face_descriptor']))
                face_distance = self.cosine_distance(known_embedding, unknown_embedding)
                
                confidence = max(0.0, min(1.0, 1.0 - face_distance))
                log_debug(f"FACE DISTANCE: {face_distance}")

                match = bool(face_distance < self.tolerance and confidence > 0.65)

                log_debug(f"Distance: {face_distance:.4f}, Confidence: {confidence:.4f}, Match: {match}")
                
                # Logger la tentative
                self.log_attempt(user_id, match, confidence, log_ip, log_user_agent)
                
                return {
                    'success': True,
                    'match': match,
                    'confidence': confidence,
                    'distance': face_distance,
                    'threshold': self.tolerance
                }
                
            except mysql.connector.Error as err:
                return {
                    'success': False,
                    'match': False,
                    'error': f'Erreur base de donnees: {str(err)}',
                    'code': 'DB_ERROR'
                }
            finally:
                if cursor:
                    cursor.close()
                conn.close()
                
        except Exception as e:
            log_debug(f"EXCEPTION: {str(e)}")
            self.log_attempt(user_id, False, 0.0, log_ip, log_user_agent)
            return {
                'success': False,
                'match': False,
                'error': str(e),
                'code': 'UNKNOWN_ERROR'
            }

    def log_attempt(self, user_id, success, confidence, ip_address=None, user_agent=None):
        """Logger une tentative de reconnaissance"""
        try:
            conn = self.get_db_connection()
            if not conn:
                return
            
            cursor = conn.cursor()
            query = """
                INSERT INTO face_recognition_log 
                (utilisateur_id, success, confidence, ip_address, user_agent, created_at)
                VALUES (%s, %s, %s, %s, %s, %s)
            """
            
            cursor.execute(query, (
                user_id,
                1 if success else 0,
                float(confidence),
                ip_address or '',
                user_agent or '',
                datetime.now().strftime('%Y-%m-%d %H:%M:%S')
            ))
            
            conn.commit()
            cursor.close()
            conn.close()
            
        except Exception as e:
            log_debug(f"Log attempt failed: {e}")

    def disable_face_recognition(self, user_id):
        """Désactiver la reconnaissance faciale"""
        try:
            conn = self.get_db_connection()
            if not conn:
                return {'success': False, 'error': 'Erreur de connexion a la base de donnees'}
            
            cursor = conn.cursor()
            cursor.execute(
                "UPDATE utilisateur SET face_enabled = 0 WHERE id = %s",
                (user_id,)
            )
            conn.commit()
            cursor.close()
            conn.close()
            
            return {'success': True, 'message': 'Reconnaissance faciale desactivee'}
            
        except Exception as e:
            return {'success': False, 'error': str(e)}

    def delete_face_data(self, user_id):
        """Supprimer les données faciales"""
        try:
            conn = self.get_db_connection()
            if not conn:
                return {'success': False, 'error': 'Erreur de connexion a la base de donnees'}
            
            cursor = conn.cursor()
            cursor.execute(
                "UPDATE utilisateur SET face_descriptor = NULL, face_enabled = 0, face_enrolled_at = NULL WHERE id = %s",
                (user_id,)
            )
            conn.commit()
            cursor.close()
            conn.close()
            
            return {'success': True, 'message': 'Donnees faciales supprimees'}
            
        except Exception as e:
            return {'success': False, 'error': str(e)}


def main():
    """Point d'entrée principal"""
    
    if len(sys.argv) < 2:
        safe_output({
            'success': False,
            'error': 'Usage: python face_recognition_service.py <action> [args...]'
        })
        sys.exit(1)
    
    action = sys.argv[1]
    log_debug(f"Action: {action}")
    log_debug(f"Args count: {len(sys.argv)}")
    
    service = FaceRecognitionService()
    result = {}
    
    try:
        if action == 'enroll':
            if len(sys.argv) < 5:
                result = {
                    'success': False,
                    'error': 'Arguments manquants pour enroll'
                }
            else:
                image_source = sys.argv[2]
                user_id = int(sys.argv[3])
                source_type = sys.argv[4]
                result = service.enroll_face(image_source, user_id, source_type)
        
        elif action == 'verify':
            if len(sys.argv) < 5:
                result = {
                    'success': False,
                    'error': 'Arguments manquants pour verify'
                }
            else:
                image_source = sys.argv[2]
                user_id = int(sys.argv[3])
                source_type = sys.argv[4]
                ip = sys.argv[5] if len(sys.argv) > 5 else None
                user_agent = sys.argv[6] if len(sys.argv) > 6 else None
                result = service.verify_face(image_source, user_id, source_type, ip, user_agent)
        
        elif action == 'disable':
            if len(sys.argv) < 3:
                result = {'success': False, 'error': 'User ID manquant'}
            else:
                user_id = int(sys.argv[2])
                result = service.disable_face_recognition(user_id)
        
        elif action == 'delete':
            if len(sys.argv) < 3:
                result = {'success': False, 'error': 'User ID manquant'}
            else:
                user_id = int(sys.argv[2])
                result = service.delete_face_data(user_id)
        
        elif action == 'test':
            result = {
                'success': True,
                'message': 'OpenCV face recognition service is working',
                'python_version': sys.version,
                'opencv_version': cv2.__version__,
                'platform': sys.platform
            }
        
        else:
            result = {'success': False, 'error': f'Action invalide: {action}'}
    
    except ValueError as ve:
        result = {
            'success': False,
            'error': f'Erreur de valeur: {str(ve)}',
            'code': 'VALUE_ERROR'
        }
    except Exception as e:
        result = {
            'success': False,
            'error': str(e),
            'code': 'EXCEPTION'
        }
    
    safe_output(result)


if __name__ == '__main__':
    main()