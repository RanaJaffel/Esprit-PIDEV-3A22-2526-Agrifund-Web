<?php
// src/Service/FaceRecognitionService.php

namespace App\Service;

use App\Entity\FaceRecognitionLog;
use App\Entity\Utilisateur;
use App\Repository\FaceRecognitionLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Psr\Log\LoggerInterface;

class FaceRecognitionService
{
    private string $pythonPath;
    private string $scriptPath;
    private string $tempDir;
    private EntityManagerInterface $entityManager;
    private FaceRecognitionLogRepository $logRepository;
    private RequestStack $requestStack;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        FaceRecognitionLogRepository $logRepository,
        RequestStack $requestStack,
        LoggerInterface $logger,
        string $projectDir
    ) {
        $this->entityManager = $entityManager;
        $this->logRepository = $logRepository;
        $this->requestStack = $requestStack;
        $this->logger = $logger;
        
        $this->pythonPath = 'C:\\Users\\SS TECH\\AppData\\Local\\Programs\\Python\\Python310\\python.exe';
        $this->scriptPath = $projectDir . '/python/face_recognition_service.py';
        $this->tempDir = sys_get_temp_dir();
        
        if (!file_exists($this->scriptPath)) {
            throw new \RuntimeException("Script Python introuvable: {$this->scriptPath}");
        }
    }

    /**
     * Clean base64 string - remove data URI prefix and whitespace
     */
    private function cleanBase64(string $base64Data): string
    {
        // Remove data URI prefix: "data:image/png;base64," or "data:image/jpeg;base64,"
        if (preg_match('/^data:image\/[a-zA-Z]+;base64,/', $base64Data, $matches)) {
            $base64Data = substr($base64Data, strlen($matches[0]));
        }

        // Remove any whitespace/newlines
        $base64Data = preg_replace('/\s+/', '', $base64Data);

        return $base64Data;
    }

    /**
     * Write base64 image to a temporary file, return the file path
     */
    private function writeBase64ToTempFile(string $base64Data): string
    {
        $cleaned = $this->cleanBase64($base64Data);

        if (empty($cleaned)) {
            throw new \RuntimeException('Base64 data is empty after cleaning');
        }

        // Validate base64
        if (!preg_match('/^[A-Za-z0-9+\/=]+$/', $cleaned)) {
            throw new \RuntimeException('Invalid base64 characters detected');
        }

        $imageData = base64_decode($cleaned, true);
        if ($imageData === false) {
            throw new \RuntimeException('Failed to decode base64 data');
        }

        if (strlen($imageData) < 100) {
            throw new \RuntimeException('Decoded image data too small (' . strlen($imageData) . ' bytes)');
        }

        // Create temp file with proper extension
        $tempFile = $this->tempDir . DIRECTORY_SEPARATOR . 'face_' . uniqid() . '.png';

        $written = file_put_contents($tempFile, $imageData);
        if ($written === false) {
            throw new \RuntimeException('Failed to write temp file: ' . $tempFile);
        }

        $this->logger->info("Temp image created", [
            'path' => $tempFile,
            'size_bytes' => $written
        ]);

        return $tempFile;
    }

    /**
     * Clean up temporary file
     */
    private function cleanupTempFile(?string $path): void
    {
        if ($path && file_exists($path)) {
            @unlink($path);
            $this->logger->debug("Temp file cleaned up", ['path' => $path]);
        }
    }

    /**
     * Execute Python command - args must be short strings only (paths, IDs)
     */
    private function executePythonCommand(array $args): array
    {
        // Safety: ensure no arg is too long
        foreach ($args as $i => $arg) {
            if (strlen($arg) > 4000) {
                throw new \RuntimeException(
                    "Argument $i is too long (" . strlen($arg) . " bytes). Use temp file instead."
                );
            }
        }

        $escapedArgs = array_map('escapeshellarg', $args);

        $command = sprintf(
            '"%s" "%s" %s 2>&1',
            $this->pythonPath,
            $this->scriptPath,
            implode(' ', $escapedArgs)
        );

        $this->logger->info("Executing Python command", [
            'action' => $args[0] ?? 'unknown',
            'user_id' => $args[2] ?? 'unknown'
        ]);

        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        $rawOutput = implode("\n", $output);

        // ✅ Force UTF-8 encoding to prevent malformed UTF-8 errors
        $cleanOutput = $this->forceUtf8($rawOutput);

        $this->logger->info("Python output", [
            'return_code' => $returnCode,
            'output_length' => strlen($cleanOutput),
            'output_preview' => mb_substr($cleanOutput, 0, 500, 'UTF-8')
        ]);

        // Try to find JSON in the output (Python might print warnings before JSON)
        $jsonString = $this->extractJson($cleanOutput);

        if ($jsonString === null) {
            $this->logger->error("No JSON found in Python output", [
                'raw_output' => mb_substr($cleanOutput, 0, 1000, 'UTF-8')
            ]);

            return [
                'success' => false,
                'error' => 'No valid JSON in Python output',
                'raw_output' => mb_substr($cleanOutput, 0, 500, 'UTF-8')
            ];
        }

        $result = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error("JSON decode error", [
                'error' => json_last_error_msg(),
                'json_string' => mb_substr($jsonString, 0, 500, 'UTF-8')
            ]);

            return [
                'success' => false,
                'error' => 'JSON decode error: ' . json_last_error_msg()
            ];
        }

        return $result;
    }

    /**
     * Force a string to valid UTF-8, removing any invalid bytes
     */
    private function forceUtf8(string $input): string
    {
        // Option 1: If it's already valid UTF-8, return as-is
        if (mb_check_encoding($input, 'UTF-8')) {
            return $input;
        }

        // Option 2: Try to convert from various encodings
        $converted = mb_convert_encoding($input, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252, ASCII');

        // Option 3: Strip any remaining invalid bytes
        $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x80-\xFF]/', '', $converted);

        return $cleaned ?: '';
    }

    /**
     * Extract the first valid JSON object or array from a string
     * (Handles cases where Python prints warnings before the JSON)
     */
    private function extractJson(string $output): ?string
    {
        // Try the entire output first
        $trimmed = trim($output);
        if ($this->isValidJson($trimmed)) {
            return $trimmed;
        }

        // Look for JSON object { ... }
        if (preg_match('/\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\}/', $output, $matches)) {
            if ($this->isValidJson($matches[0])) {
                return $matches[0];
            }
        }

        // Try each line (last line often contains the JSON)
        $lines = array_reverse(explode("\n", $output));
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && $this->isValidJson($line)) {
                return $line;
            }
        }

        return null;
    }

    /**
     * Check if a string is valid JSON
     */
    private function isValidJson(string $str): bool
    {
        if (empty($str)) {
            return false;
        }
        json_decode($str);
        return json_last_error() === JSON_ERROR_NONE;
    }

    // ================================================================
    // ENROLL METHODS
    // ================================================================

    /**
     * Enroll face from base64 image
     */
    public function enrollFaceFromBase64(string $imageBase64, Utilisateur $user): array
    {
        $tempFile = null;

        try {
            $this->logger->info("=== ENROLL FACE START ===", [
                'user_id' => $user->getId(),
                'base64_length' => strlen($imageBase64)
            ]);

            // ✅ Convert base64 to temp file - avoids shell argument limits
            $tempFile = $this->writeBase64ToTempFile($imageBase64);

            // ✅ Pass file path (short string), not base64 (huge string)
            $result = $this->executePythonCommand([
                'enroll',
                $tempFile,
                (string) $user->getId(),
                'file'
            ]);

            if ($result['success'] ?? false) {
                $this->entityManager->refresh($user);
                $this->logger->info("✅ Face enrolled successfully");
            } else {
                $this->logger->error("❌ Enroll failed", [
                    'error' => $result['error'] ?? 'Unknown'
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            $this->logger->error("EXCEPTION during enroll", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => 'EXCEPTION'
            ];
        } finally {
            $this->cleanupTempFile($tempFile);
        }
    }

    /**
     * Enroll face from file path
     */
    public function enrollFaceFromFile(string $filePath, Utilisateur $user): array
    {
        try {
            if (!file_exists($filePath)) {
                return [
                    'success' => false,
                    'error' => 'File not found',
                    'code' => 'FILE_NOT_FOUND'
                ];
            }

            $result = $this->executePythonCommand([
                'enroll',
                $filePath,
                (string) $user->getId(),
                'file'
            ]);

            if ($result['success'] ?? false) {
                $this->entityManager->refresh($user);
            }

            return $result;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => 'EXCEPTION'
            ];
        }
    }

    // ================================================================
    // VERIFY METHODS
    // ================================================================

    /**
     * Verify face from base64 image
     */
    public function verifyFaceFromBase64(string $imageBase64, Utilisateur $user): array
    {
        $tempFile = null;

        try {
            $request = $this->requestStack->getCurrentRequest();
            $ip = $request ? ($request->getClientIp() ?? '') : '';
            $userAgent = $request ? ($request->headers->get('User-Agent', '') ?? '') : '';

            $this->logger->info("=== VERIFY FACE START ===", [
                'user_id' => $user->getId(),
                'ip' => $ip
            ]);

            // ✅ Convert base64 to temp file
            $tempFile = $this->writeBase64ToTempFile($imageBase64);

            $result = $this->executePythonCommand([
                'verify',
                $tempFile,
                (string) $user->getId(),
                'file',
                $ip,
                $userAgent
            ]);

            if ($result['success'] ?? false) {
                $this->logger->info("Verification result", [
                    'match' => $result['match'] ?? false,
                    'confidence' => $result['confidence'] ?? 0.0
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            $this->logger->error("Exception during verify", [
                'message' => $e->getMessage()
            ]);
            $this->logFailedAttempt($user);

            return [
                'success' => false,
                'match' => false,
                'error' => $e->getMessage(),
                'code' => 'EXCEPTION'
            ];
        } finally {
            $this->cleanupTempFile($tempFile);
        }
    }

    /**
     * Verify face from file path
     */
    public function verifyFaceFromFile(string $filePath, Utilisateur $user): array
    {
        try {
            if (!file_exists($filePath)) {
                return [
                    'success' => false,
                    'match' => false,
                    'error' => 'File not found',
                    'code' => 'FILE_NOT_FOUND'
                ];
            }

            $request = $this->requestStack->getCurrentRequest();
            $ip = $request ? ($request->getClientIp() ?? '') : '';
            $userAgent = $request ? ($request->headers->get('User-Agent', '') ?? '') : '';

            $result = $this->executePythonCommand([
                'verify',
                $filePath,
                (string) $user->getId(),
                'file',
                $ip,
                $userAgent
            ]);

            return $result;

        } catch (\Exception $e) {
            $this->logFailedAttempt($user);

            return [
                'success' => false,
                'match' => false,
                'error' => $e->getMessage(),
                'code' => 'EXCEPTION'
            ];
        }
    }

    // ================================================================
    // MANAGEMENT METHODS
    // ================================================================

    public function disableFaceRecognition(Utilisateur $user): array
    {
        try {
            $result = $this->executePythonCommand([
                'disable',
                (string) $user->getId()
            ]);

            if ($result['success'] ?? false) {
                $this->entityManager->refresh($user);
            }

            return $result;

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteFaceData(Utilisateur $user): array
    {
        try {
            $result = $this->executePythonCommand([
                'delete',
                (string) $user->getId()
            ]);

            if ($result['success'] ?? false) {
                $this->entityManager->refresh($user);
            }

            return $result;

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ================================================================
    // LOGGING & STATS
    // ================================================================

    private function logFailedAttempt(Utilisateur $user): void
    {
        try {
            $request = $this->requestStack->getCurrentRequest();

            $log = new FaceRecognitionLog();
            $log->setUtilisateur($user);
            $log->setSuccess(false);
            $log->setConfidence(0.0);

            if ($request) {
                $log->setIpAddress($request->getClientIp());
                $log->setUserAgent($request->headers->get('User-Agent'));
            }

            $this->entityManager->persist($log);
            $this->entityManager->flush();

        } catch (\Exception $e) {
            $this->logger->error("Failed to log attempt", ['error' => $e->getMessage()]);
        }
    }

    public function hasToManyFailedAttempts(Utilisateur $user, int $maxAttempts = 5, int $minutes = 15): bool
    {
        $since = new \DateTime("-{$minutes} minutes");
        return $this->logRepository->countFailedAttempts($user, $since) >= $maxAttempts;
    }

    public function getRecentAttempts(Utilisateur $user, int $limit = 10): array
    {
        return $this->logRepository->findRecentAttempts($user, $limit);
    }

    public function getSuccessRate(Utilisateur $user): float
    {
        return $this->logRepository->getSuccessRate($user);
    }
}