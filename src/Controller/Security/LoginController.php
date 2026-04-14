<?php
// src/Controller/Security/LoginController.php

namespace App\Controller\Security;

use App\Service\TwoFactorAuthService;
use Doctrine\ORM\EntityManagerInterface;
use ReCaptcha\ReCaptcha;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(
        AuthenticationUtils $authenticationUtils,
        Request $request
    ): Response {
        // Si déjà connecté, rediriger selon le rôle
        if ($this->getUser()) {
            // Vérifier si la 2FA est requise
            $user = $this->getUser();
            if ($user->has2FAEnabled()) {
                $session = $request->getSession();
                if (!$session->get('2fa_verified')) {
                    return $this->redirectToRoute('app_login_2fa');
                }
            }
            return $this->redirectToRoute($this->getRedirectRoute());
        }

        // Vérifier le CAPTCHA si le formulaire est soumis
        if ($request->isMethod('POST')) {
            $recaptchaResponse = $request->request->get('g-recaptcha-response');
            
            if (empty($recaptchaResponse)) {
                $this->addFlash('error', 'Veuillez cocher la case "Je ne suis pas un robot"');
            } else {
                $recaptcha = new ReCaptcha($this->getParameter('recaptcha_secret_key'));
                $resp = $recaptcha->setExpectedHostname($request->getHost())
                                  ->verify($recaptchaResponse, $request->getClientIp());
                
                if (!$resp->isSuccess()) {
                    $errors = $resp->getErrorCodes();
                    $this->addFlash('error', 'Validation CAPTCHA échouée. Veuillez réessayer.');
                    
                    // Log les erreurs pour le débogage
                    error_log('reCAPTCHA errors: ' . implode(', ', $errors));
                }
            }
        }

        // Récupérer l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Dernier nom d'utilisateur entré
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'recaptcha_site_key' => $this->getParameter('recaptcha_site_key'),
        ]);
    }

    #[Route('/login/2fa', name: 'app_login_2fa')]
    public function verify2FA(
        Request $request,
        TwoFactorAuthService $twoFactorService,
        EntityManagerInterface $em
    ): Response {
        // Vérifier si l'utilisateur est déjà connecté
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Veuillez vous connecter d\'abord.');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier si 2FA est activé
        if (!$user->has2FAEnabled()) {
            return $this->redirectToRoute($this->getRedirectRoute());
        }

        // Vérifier si le code 2FA a déjà été validé dans cette session
        if ($request->getSession()->get('2fa_verified')) {
            return $this->redirectToRoute($this->getRedirectRoute());
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $code = $request->request->get('code');

            if ($twoFactorService->verifierCode($user, $code)) {
                // Code valide - marquer comme vérifié dans la session
                $request->getSession()->set('2fa_verified', true);
                
                // MAINTENANT on met à jour la dernière connexion et le statut en ligne
                $user->setDerniereConnexion(new \DateTime());
                $user->setEstEnLigne(true);
                $em->flush();

                $this->addFlash('success', 'Authentification réussie ! Bienvenue ' . $user->getNomComplet());
                return $this->redirectToRoute($this->getRedirectRoute());
            } else {
                $error = 'Code invalide ou expiré. Veuillez réessayer.';
                $this->addFlash('error', $error);
            }
        }

        return $this->render('security/verify_2fa.html.twig', [
            'error' => $error,
            'utilisateur' => $user
        ]);
    }

    #[Route('/login/2fa/resend', name: 'app_login_2fa_resend')]
    public function resend2FA(
        TwoFactorAuthService $twoFactorService
    ): Response {
        $user = $this->getUser();
        
        if (!$user || !$user->has2FAEnabled()) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $twoFactorService->creerEtEnvoyerCode($user);
            $this->addFlash('success', 'Un nouveau code a été envoyé à votre adresse email !');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'envoi du code. Veuillez réessayer.');
        }

        return $this->redirectToRoute('app_login_2fa');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    private function getRedirectRoute(): string
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return 'admin_dashboard';
        } elseif ($this->isGranted('ROLE_AGRICULTEUR')) {
            return 'agriculteur_dashboard';
        } elseif ($this->isGranted('ROLE_BANQUE')) {
            return 'banque_dashboard';
        }
        
        return 'app_login';
    }
}