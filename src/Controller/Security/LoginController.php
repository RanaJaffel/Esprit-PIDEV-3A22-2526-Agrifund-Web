<?php
// src/Controller/Security/LoginController.php

namespace App\Controller\Security;

use App\Entity\Utilisateur;
use App\Service\TwoFactorAuthService;
use App\Service\FaceRecognitionService;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use ReCaptcha\ReCaptcha;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Psr\Log\LoggerInterface;


class LoginController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    // =================================================================
    // LOGIN CLASSIQUE
    // =================================================================

    #[Route('/login', name: 'app_login')]
    public function login(
        AuthenticationUtils $authenticationUtils,
        Request $request
    ): Response {
        $user = $this->currentUtilisateur();

        if ($user !== null) {
            if ($request->getSession()->get('2fa_pending_password_login') && !$request->getSession()->get('2fa_verified')) {
                return $this->redirectToRoute('app_login_2fa');
            }
            return $this->redirectToRoute($this->getRedirectRoute());
        }

        if ($request->isMethod('POST')) {
            $recaptchaResponse = $request->request->getString('g-recaptcha-response');

            if ($recaptchaResponse === '') {
                $this->addFlash('error', 'Veuillez cocher la case "Je ne suis pas un robot"');
            } else {
                $recaptcha = new ReCaptcha($this->getParameter('recaptcha_secret_key'));
                $resp = $recaptcha
                    ->setExpectedHostname($request->getHost())
                    ->verify($recaptchaResponse, $request->getClientIp());

                if (!$resp->isSuccess()) {
                    $this->addFlash('error', 'Validation CAPTCHA échouée. Veuillez réessayer.');
                    $this->logger->warning('reCAPTCHA failed', [
                        'errors' => $resp->getErrorCodes()
                    ]);
                }
            }
        }

        $error        = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username'     => $lastUsername,
            'error'             => $error,
            'recaptcha_site_key'=> $this->getParameter('recaptcha_site_key'),
        ]);
    }

    // =================================================================
    // PAGE LOGIN FACIAL
    // =================================================================

    #[Route('/login/face', name: 'app_login_face')]
    public function loginFace(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute($this->getRedirectRoute());
        }

        return $this->render('security/login_face.html.twig');
    }

    // =================================================================
    // API VÉRIFICATION FACIALE
    // =================================================================

    #[Route('/login/face/verify', name: 'app_login_face_verify', methods: ['POST'])]
    public function verifyFaceLogin(
        Request $request,
        FaceRecognitionService $faceService,
        UtilisateurRepository $userRepository,
        TokenStorageInterface $tokenStorage,
        EntityManagerInterface $em
    ): JsonResponse {

        // ---------------------------------------------------------
        // 1. Décoder la requête JSON
        // ---------------------------------------------------------
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->safeJson([
                'success' => false,
                'error'   => 'Requête JSON invalide'
            ], 400);
        }

        $imageBase64 = $data['image'] ?? null;
        $email       = $data['email'] ?? null;

        // ---------------------------------------------------------
        // 2. Valider les données
        // ---------------------------------------------------------
        if (!$email || !is_string($email)) {
            return $this->safeJson([
                'success' => false,
                'error'   => 'Email manquant'
            ], 400);
        }

        if (!$imageBase64 || !is_string($imageBase64) || strlen($imageBase64) < 100) {
            return $this->safeJson([
                'success' => false,
                'error'   => 'Image manquante ou invalide'
            ], 400);
        }

        // ---------------------------------------------------------
        // 3. Trouver l'utilisateur
        // ---------------------------------------------------------
        $user = $userRepository->findOneByEmail(trim($email));

        if (!$user) {
            $this->logger->warning("Face login: user not found", ['email' => $email]);
            return $this->safeJson([
                'success' => false,
                'error'   => 'Aucun compte trouvé avec cet email'
            ], 404);
        }

        // ---------------------------------------------------------
        // 4. Debug - Logger l'état du compte
        // ---------------------------------------------------------
        $this->logger->info("=== FACE LOGIN ATTEMPT ===", [
            'user_id'         => $user->getId(),
            'email'           => $user->getEmail(),
            'is_verified'     => $user->isVerified(),
            'face_enabled'    => $user->isFaceEnabled(),
            'has_face'        => $user->hasFaceEnrolled(),
            'can_login'       => $user->canLogin(),
            'can_login_face'  => $user->canLoginWithFace(),
        ]);

        // ---------------------------------------------------------
        // 5. ✅ Vérifier que la reconnaissance faciale est dispo
        //    On N'utilise PAS canLogin() car il vérifie isVerified
        // ---------------------------------------------------------
        if (!$user->canLoginWithFace()) {
            $reason = 'Reconnaissance faciale non configurée pour ce compte';

            if (!$user->isFaceEnabled()) {
                $reason = 'La reconnaissance faciale n\'est pas activée pour ce compte';
            } elseif (!$user->hasFaceEnrolled()) {
                $reason = 'Aucun visage enregistré pour ce compte';
            }

            $this->logger->warning("Face login blocked: face not available", [
                'user_id' => $user->getId(),
                'reason'  => $reason
            ]);

            return $this->safeJson([
                'success' => false,
                'error'   => $reason
            ], 403);
        }

        // ---------------------------------------------------------
        // 6. Vérifier le rate limiting
        // ---------------------------------------------------------
        if ($faceService->hasToManyFailedAttempts($user)) {
            $this->logger->warning("Face login blocked: too many attempts", [
                'user_id' => $user->getId()
            ]);
            return $this->safeJson([
                'success' => false,
                'error'   => 'Trop de tentatives échouées. Réessayez dans 15 minutes.'
            ], 429);
        }

        // ---------------------------------------------------------
        // 7. Vérifier le visage via Python
        // ---------------------------------------------------------
        $this->logger->info("Calling face verification", [
            'user_id' => $user->getId()
        ]);

        try {
            $result = $faceService->verifyFaceFromBase64($imageBase64, $user);
        } catch (\Exception $e) {
            $this->logger->error("Face service exception", [
                'message' => $e->getMessage()
            ]);
            return $this->safeJson([
                'success' => false,
                'error'   => 'Erreur du service de reconnaissance faciale'
            ], 500);
        }

        $this->logger->info("Face verification result", [
            'user_id'    => $user->getId(),
            'success'    => $result['success'] ?? false,
            'match'      => $result['match'] ?? false,
            'confidence' => $result['confidence'] ?? 0,
        ]);

        // ---------------------------------------------------------
        // 8. Gérer les erreurs techniques du service
        // ---------------------------------------------------------
        if (!($result['success'] ?? false)) {
            $errorCode = $result['code'] ?? 'UNKNOWN';

            $errorMessages = [
                'NO_FACE'          => 'Aucun visage détecté. Placez votre visage face à la caméra.',
                'MULTIPLE_FACES'   => 'Plusieurs visages détectés. Assurez-vous d\'être seul(e).',
                'FACE_TOO_SMALL'   => 'Visage trop petit. Rapprochez-vous de la caméra.',
                'ENCODING_FAILED'  => 'Impossible d\'analyser le visage. Réessayez.',
                'DB_ERROR'         => 'Erreur de base de données. Contactez l\'administrateur.',
                'NO_FACE_ENROLLED' => 'Aucun visage enregistré pour ce compte.',
                'FACE_DISABLED'    => 'Reconnaissance faciale désactivée pour ce compte.',
            ];

            $errorMessage = $errorMessages[$errorCode]
                ?? ($result['error'] ?? 'Erreur de vérification du visage');

            return $this->safeJson([
                'success' => false,
                'error'   => $errorMessage,
                'code'    => $errorCode
            ], 400);
        }

        // ---------------------------------------------------------
        // 9. Visage non reconnu
        // ---------------------------------------------------------
        if (!($result['match'] ?? false)) {
            $confidence = $result['confidence'] ?? 0;

            $this->logger->warning("Face login: face not matched", [
                'user_id'    => $user->getId(),
                'confidence' => $confidence
            ]);

            return $this->safeJson([
                'success'    => false,
                'match'      => false,
                'confidence' => $confidence,
                'error'      => 'Visage non reconnu. Veuillez réessayer.'
            ], 401);
        }

        // ---------------------------------------------------------
        // 10. ✅ SUCCÈS - Connexion par visage réussie
        // ---------------------------------------------------------
        $this->logger->info("✅ FACE LOGIN SUCCESS", [
            'user_id'    => $user->getId(),
            'email'      => $user->getEmail(),
            'confidence' => $result['confidence'] ?? 0
        ]);

        // ✅ Auto-vérifier le compte si pas encore vérifié
        if (!$user->isVerified()) {
            $this->logger->info("Auto-verifying account after successful face login", [
                'user_id' => $user->getId()
            ]);
            $user->setIsVerified(true);
        }

        $user->setDerniereConnexion(new \DateTime());
        $user->setEstEnLigne(true);
        $em->flush();

        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $tokenStorage->setToken($token);
        $request->getSession()->set('_security_main', serialize($token));
        $request->getSession()->remove('2fa_pending_password_login');
        $request->getSession()->remove('2fa_verified');

        // ---------------------------------------------------------
        // 12. Retourner la réponse avec redirect
        // ---------------------------------------------------------
        $redirectUrl = $this->generateUrl($this->getRedirectRoute());

        return $this->safeJson([
            'success'    => true,
            'match'      => true,
            'confidence' => $result['confidence'] ?? 0,
            'require2fa' => false,
            'redirect'   => $redirectUrl,
            'message'    => 'Connexion réussie ! Bienvenue ' . $user->getNomComplet()
        ]);
    }

    // =================================================================
    // 2FA
    // =================================================================

    #[Route('/login/2fa', name: 'app_login_2fa')]
    public function verify2FA(
        Request $request,
        TwoFactorAuthService $twoFactorService,
        EntityManagerInterface $em
    ): Response {
        $user = $this->currentUtilisateur();

        if (!$user) {
            $this->addFlash('error', 'Veuillez vous connecter d\'abord.');
            return $this->redirectToRoute('app_login');
        }

        if (!$request->getSession()->get('2fa_pending_password_login')) {
            return $this->redirectToRoute($this->getRedirectRoute());
        }

        if ($request->getSession()->get('2fa_verified')) {
            $request->getSession()->remove('2fa_pending_password_login');
            return $this->redirectToRoute($this->getRedirectRoute());
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $code = trim($request->request->getString('code'));

            if ($code !== '' && $twoFactorService->verifierCode($user, $code)) {
                $request->getSession()->set('2fa_verified', true);
                $request->getSession()->remove('2fa_pending_password_login');

                $user->setDerniereConnexion(new \DateTime());
                $user->setEstEnLigne(true);
                $em->flush();

                $this->addFlash('success', 'Authentification réussie ! Bienvenue ' . $user->getNomComplet());
                return $this->redirectToRoute($this->getRedirectRoute());
            }

            $error = 'Code invalide ou expiré. Veuillez réessayer.';
            $this->addFlash('error', $error);
        }

        return $this->render('security/verify_2fa.html.twig', [
            'error'       => $error,
            'utilisateur' => $user
        ]);
    }

    #[Route('/login/2fa/resend', name: 'app_login_2fa_resend')]
    public function resend2FA(Request $request, TwoFactorAuthService $twoFactorService): Response
    {
        $user = $this->currentUtilisateur();

        if (!$user || !$request->getSession()->get('2fa_pending_password_login')) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $twoFactorService->creerEtEnvoyerCode($user);
            $this->addFlash('success', 'Un nouveau code a été envoyé à votre adresse email !');
        } catch (\Exception $e) {
            $this->logger->error("Failed to resend 2FA", ['error' => $e->getMessage()]);
            $this->addFlash('error', 'Erreur lors de l\'envoi du code. Veuillez réessayer.');
        }

        return $this->redirectToRoute('app_login_2fa');
    }

    // =================================================================
    // LOGOUT
    // =================================================================

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Intercepted by firewall.');
    }

    // =================================================================
    // HELPERS PRIVÉS
    // =================================================================

    /**
     * ✅ JSON response safe UTF-8
     *
     * @param array<string, mixed> $data
     */
    private function safeJson(array $data, int $status = 200): JsonResponse
    {
        $sanitized = $this->sanitizeForJson($data);

        $json = json_encode(
            $sanitized,
            JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR
        );

        if ($json === false) {
            $this->logger->error("JSON encode failed", [
                'error' => json_last_error_msg()
            ]);
            $json   = json_encode(['success' => false, 'error' => 'Encoding error']);
            $status = 500;
        }

        return new JsonResponse($json, $status, [], true);
    }

    private function currentUtilisateur(): ?Utilisateur
    {
        $user = $this->getUser();

        return $user instanceof Utilisateur ? $user : null;
    }

    /**
     * ✅ Nettoyer récursivement pour JSON
     */
    private function sanitizeForJson(mixed $data): mixed
    {
        if (is_string($data)) {
            $clean = mb_convert_encoding($data, 'UTF-8', 'UTF-8');
            return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $clean) ?? $data;
        }

        if (is_array($data)) {
            return array_map([$this, 'sanitizeForJson'], $data);
        }

        return $data;
    }

    /**
     * Redirection selon le rôle
     */
    private function getRedirectRoute(): string
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return 'admin_dashboard';
        }
        if ($this->isGranted('ROLE_AGRICULTEUR')) {
            return 'agriculteur_dashboard';
        }
        if ($this->isGranted('ROLE_BANQUE')) {
            return 'banque_dashboard';
        }
        return 'app_login';
    }
}
