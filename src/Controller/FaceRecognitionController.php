<?php
// src/Controller/FaceRecognitionController.php

namespace App\Controller;

use App\Service\FaceRecognitionService;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Psr\Log\LoggerInterface;

#[Route('/face')]
class FaceRecognitionController extends AbstractController
{
    public function __construct(
        private FaceRecognitionService $faceService,
        private LoggerInterface $logger,
        private UtilisateurRepository $utilisateurRepository,
    ) {}

    /**
     * Force all values in an array to valid UTF-8
     */
    private function sanitizeForJson(mixed $data): mixed
    {
        if (is_string($data)) {
            // Remove any non-UTF-8 bytes
            $clean = mb_convert_encoding($data, 'UTF-8', 'UTF-8');
            // Strip null bytes and control characters (except newline/tab)
            return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $clean);
        }

        if (is_array($data)) {
            return array_map([$this, 'sanitizeForJson'], $data);
        }

        // int, float, bool, null — all JSON-safe
        return $data;
    }

    /**
     * Return a UTF-8 safe JSON response
     */
    private function safeJson(array $data, int $status = 200): JsonResponse
    {
        $sanitized = $this->sanitizeForJson($data);

        // Manual JSON encode with error handling
        $json = json_encode($sanitized, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);

        if ($json === false) {
            $this->logger->error('JSON encode failed even after sanitization', [
                'error' => json_last_error_msg()
            ]);

            $json = json_encode([
                'success' => false,
                'error' => 'Internal encoding error'
            ]);
            $status = 500;
        }

        return new JsonResponse($json, $status, [], true); // true = json is already a string
    }

    /**
     * Page de configuration de la reconnaissance faciale
     */
    #[Route('/setup', name: 'face_setup')]
    #[IsGranted('ROLE_USER')]
    public function setup(): Response
    {
        $user = $this->getUser();

        return $this->render('face/setup.html.twig', [
            'user' => $user,
            'hasFaceEnrolled' => $user->hasFaceEnrolled(),
            'recentAttempts' => $this->faceService->getRecentAttempts($user, 5),
            'successRate' => $this->faceService->getSuccessRate($user)
        ]);
    }

    /**
     * Enregistrer un visage (API)
     */
    #[Route('/enroll', name: 'face_enroll', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function enroll(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->safeJson([
                'success' => false,
                'error' => 'Non authentifié'
            ], 401);
        }

        // ✅ Decode request body
        $content = $request->getContent();
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Invalid JSON in request body', [
                'error' => json_last_error_msg(),
                'content_length' => strlen($content)
            ]);

            return $this->safeJson([
                'success' => false,
                'error' => 'JSON invalide dans la requête'
            ], 400);
        }

        $imageBase64 = $data['image'] ?? null;

        if (!$imageBase64 || !is_string($imageBase64)) {
            return $this->safeJson([
                'success' => false,
                'error' => 'Image manquante ou invalide'
            ], 400);
        }

        // ✅ Basic validation of the base64 data
        $imageBase64 = trim($imageBase64);

        if (strlen($imageBase64) < 100) {
            return $this->safeJson([
                'success' => false,
                'error' => 'Image trop petite'
            ], 400);
        }

        $this->logger->info('Enroll request received', [
            'user_id' => $user->getId(),
            'image_length' => strlen($imageBase64),
            'has_data_uri' => str_starts_with($imageBase64, 'data:')
        ]);

        try {
            $result = $this->faceService->enrollFaceFromBase64($imageBase64, $user);

            $statusCode = ($result['success'] ?? false) ? 200 : 400;

            // ✅ Use safeJson to prevent UTF-8 errors
            return $this->safeJson($result, $statusCode);

        } catch (\Exception $e) {
            $this->logger->error('Enroll exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->safeJson([
                'success' => false,
                'error' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier un visage (pour login) - API
     */
    #[Route('/verify', name: 'face_verify', methods: ['POST'])]
    public function verify(Request $request): JsonResponse
    {
        $content = $request->getContent();
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->safeJson([
                'success' => false,
                'error' => 'JSON invalide'
            ], 400);
        }

        $imageBase64 = $data['image'] ?? null;
        $userId = $data['user_id'] ?? null;

        if (!$imageBase64 || !$userId) {
            return $this->safeJson([
                'success' => false,
                'error' => 'Image ou user_id manquant'
            ], 400);
        }

        // Get user from database
        $user = $this->utilisateurRepository->find((int) $userId);

        if (!$user) {
            return $this->safeJson([
                'success' => false,
                'error' => 'Utilisateur non trouvé'
            ], 404);
        }

        // Check rate limiting
        if ($this->faceService->hasToManyFailedAttempts($user)) {
            return $this->safeJson([
                'success' => false,
                'error' => 'Trop de tentatives échouées. Réessayez plus tard.'
            ], 429);
        }

        try {
            $result = $this->faceService->verifyFaceFromBase64($imageBase64, $user);

            $statusCode = ($result['success'] ?? false) ? 200 : 400;
            return $this->safeJson($result, $statusCode);

        } catch (\Exception $e) {
            $this->logger->error('Verify exception', [
                'message' => $e->getMessage()
            ]);

            return $this->safeJson([
                'success' => false,
                'match' => false,
                'error' => 'Erreur lors de la vérification'
            ], 500);
        }
    }

    /**
     * Supprimer les données faciales
     */
    #[Route('/delete', name: 'face_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->safeJson([
                'success' => false,
                'error' => 'Non authentifié'
            ], 401);
        }

        try {
            $result = $this->faceService->deleteFaceData($user);
            return $this->safeJson($result);

        } catch (\Exception $e) {
            return $this->safeJson([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Désactiver la reconnaissance faciale
     */
    #[Route('/disable', name: 'face_disable', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function disable(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->safeJson([
                'success' => false,
                'error' => 'Non authentifié'
            ], 401);
        }

        try {
            $result = $this->faceService->disableFaceRecognition($user);
            return $this->safeJson($result);

        } catch (\Exception $e) {
            return $this->safeJson([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Historique des tentatives
     */
    #[Route('/history', name: 'face_history')]
    #[IsGranted('ROLE_USER')]
    public function history(): Response
    {
        $user = $this->getUser();

        return $this->render('face/history.html.twig', [
            'attempts' => $this->faceService->getRecentAttempts($user, 50),
            'successRate' => $this->faceService->getSuccessRate($user)
        ]);
    }
}
