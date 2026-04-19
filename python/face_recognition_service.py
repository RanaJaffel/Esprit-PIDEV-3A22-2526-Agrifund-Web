#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Service de reconnaissance faciale pour AgriFund
Utilise la bibliothèque face_recognition
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

# ✅ FIX 2: Suppress noisy library warnings that pollute stdout
import warnings
warnings.filterwarnings('ignore')

# Suppress TensorFlow/dlib warnings
os.environ['TF_CPP_MIN_LOG_LEVEL'] = '3'

import face_recognition
import numpy as np
from PIL import Image
import mysql.connector


def safe_output(result: dict):
    """
    ✅ FIX 3: Guaranteed clean JSON output to stdout
    This is the ONLY function that prints to stdout
    """
    try:
        # ensure_ascii=True guarantees no non-ASCII bytes in output
        output = json.dumps(result, ensure_ascii=True)
    except (TypeError, ValueError) as e:
        output = json.dumps({
            'success': False,
            'error': f'JSON serialization error: {str(e)}',
            'code': 'JSON_ERROR'
        }, ensure_ascii=True)

    # Write directly to stdout buffer to avoid encoding issues
    if hasattr(sys.stdout, 'buffer'):
        sys.stdout.buffer.write(output.encode('utf-8'))
        sys.stdout.buffer.write(b'\n')
        sys.stdout.buffer.flush()
    else:
        print(output)


def log_debug(message: str):
    """✅ All debug output goes to stderr, never stdout"""
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
        self.tolerance = 0.6
        self.quality_threshold = 100

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
            # Remove data URI prefix if present
            if ',' in base64_string:
                base64_string = base64_string.split(',')[1]

            # Remove whitespace
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
            # ✅ FIX 4: Normalize Windows file path
            file_path = os.path.normpath(file_path)

            log_debug(f"Loading image from: {file_path}")

            if not os.path.exists(file_path):
                raise Exception(f"Fichier introuvable: {file_path}")

            file_size = os.path.getsize(file_path)
            log_debug(f"File size: {file_size} bytes")

            if file_size < 100:
                raise Exception(f"Fichier trop petit: {file_size} bytes")

            # ✅ FIX 5: Use PIL first, then convert to numpy array
            # This is more robust than face_recognition.load_image_file()
            # which can fail with certain file paths on Windows
            try:
                image = Image.open(file_path)
                if image.mode != 'RGB':
                    image = image.convert('RGB')
                image_array = np.array(image)
            except Exception as pil_error:
                log_debug(f"PIL failed, trying face_recognition: {pil_error}")
                # Fallback to face_recognition's loader
                image_array = face_recognition.load_image_file(file_path)

            log_debug(f"Image loaded: shape={image_array.shape}, dtype={image_array.dtype}")
            return image_array

        except Exception as e:
            raise Exception(f"Erreur de chargement du fichier: {str(e)}")

    def detect_and_validate_face(self, image):
        """Détecter un visage et valider sa qualité"""
        log_debug("Detecting faces...")

        face_locations = face_recognition.face_locations(image)

        log_debug(f"Found {len(face_locations)} face(s)")

        if len(face_locations) == 0:
            return {
                'success': False,
                'error': 'Aucun visage detecte. Placez votre visage face a la camera.',
                'code': 'NO_FACE'
            }

        if len(face_locations) > 1:
            return {
                'success': False,
                'error': 'Plusieurs visages detectes. Assurez-vous d\'etre seul(e) dans le cadre.',
                'code': 'MULTIPLE_FACES'
            }

        top, right, bottom, left = face_locations[0]
        face_width = right - left
        face_height = bottom - top

        log_debug(f"Face size: {face_width}x{face_height}")

        if face_width < self.quality_threshold or face_height < self.quality_threshold:
            return {
                'success': False,
                'error': 'Visage trop petit ou eloigne. Rapprochez-vous de la camera.',
                'code': 'FACE_TOO_SMALL'
            }

        return {
            'success': True,
            'location': face_locations[0]
        }

    def enroll_face(self, image_source, user_id, source_type='file'):
        """Enregistrer un visage pour un utilisateur"""
        try:
            log_debug(f"=== ENROLL START === user_id={user_id}, source_type={source_type}")

            # Load image
            if source_type == 'base64':
                image = self.load_image_from_base64(image_source)
            else:
                image = self.load_image_from_file(image_source)

            log_debug(f"Image loaded successfully: {image.shape}")

            # Detect and validate face
            validation = self.detect_and_validate_face(image)
            if not validation['success']:
                return validation

            # Encode face
            log_debug("Encoding face...")
            face_encodings = face_recognition.face_encodings(image)

            if len(face_encodings) == 0:
                return {
                    'success': False,
                    'error': 'Impossible d\'encoder le visage',
                    'code': 'ENCODING_FAILED'
                }

            face_encoding = face_encodings[0]
            log_debug(f"Face encoded: {len(face_encoding)} dimensions")

            # Save to database
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
                encoding_json = json.dumps(face_encoding.tolist())
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
                    'encoding_size': len(face_encoding)
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

            # Load image
            if source_type == 'base64':
                image = self.load_image_from_base64(image_source)
            else:
                image = self.load_image_from_file(image_source)

            # Detect and validate face
            validation = self.detect_and_validate_face(image)
            if not validation['success']:
                self.log_attempt(user_id, False, 0.0, log_ip, log_user_agent)
                return {**validation, 'match': False}

            # Encode face
            unknown_encodings = face_recognition.face_encodings(image)

            if len(unknown_encodings) == 0:
                self.log_attempt(user_id, False, 0.0, log_ip, log_user_agent)
                return {
                    'success': False,
                    'match': False,
                    'error': 'Impossible d\'encoder le visage',
                    'code': 'ENCODING_FAILED'
                }

            unknown_encoding = unknown_encodings[0]

            # Get stored face
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

                # Compare faces
                known_encoding = np.array(json.loads(result['face_descriptor']))
                face_distances = face_recognition.face_distance([known_encoding], unknown_encoding)
                face_distance = float(face_distances[0])

                confidence = max(0.0, min(1.0, 1.0 - face_distance))
                match = face_distance <= self.tolerance

                log_debug(f"Distance: {face_distance:.4f}, Confidence: {confidence:.4f}, Match: {match}")

                # Log attempt
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
        """Désactiver la reconnaissance faciale pour un utilisateur"""
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
        """Supprimer les données faciales d'un utilisateur"""
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
    """Point d'entrée principal du script"""

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
                    'error': 'Arguments manquants pour enroll (need: image_source, user_id, source_type)',
                    'args_received': len(sys.argv)
                }
            else:
                image_source = sys.argv[2]
                user_id = int(sys.argv[3])
                source_type = sys.argv[4]

                log_debug(f"Enroll: user_id={user_id}, source_type={source_type}")

                if source_type == 'file':
                    log_debug(f"File path: {image_source}")
                    log_debug(f"File exists: {os.path.exists(image_source)}")
                else:
                    log_debug(f"Base64 length: {len(image_source)}")

                result = service.enroll_face(image_source, user_id, source_type)

        elif action == 'verify':
            if len(sys.argv) < 5:
                result = {
                    'success': False,
                    'error': 'Arguments manquants pour verify',
                    'args_received': len(sys.argv)
                }
            else:
                image_source = sys.argv[2]
                user_id = int(sys.argv[3])
                source_type = sys.argv[4]
                ip = sys.argv[5] if len(sys.argv) > 5 else None
                user_agent = sys.argv[6] if len(sys.argv) > 6 else None

                log_debug(f"Verify: user_id={user_id}, source_type={source_type}")

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
            # ✅ Simple test action to verify the script works
            result = {
                'success': True,
                'message': 'Python face recognition service is working',
                'python_version': sys.version,
                'platform': sys.platform
            }

        else:
            result = {'success': False, 'error': f'Action invalide: {action}'}

    except ValueError as ve:
        result = {
            'success': False,
            'error': f'Erreur de valeur (user_id invalide?): {str(ve)}',
            'code': 'VALUE_ERROR'
        }
    except Exception as e:
        result = {
            'success': False,
            'error': str(e),
            'code': 'EXCEPTION'
        }

    # ✅ Single clean output point
    safe_output(result)


if __name__ == '__main__':
    main()