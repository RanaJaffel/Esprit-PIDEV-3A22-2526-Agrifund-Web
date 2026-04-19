<?php
// src/Controller/Security/EmailVerificationController.php

namespace App\Controller\Security;

use App\Repository\UtilisateurRepository;
use App\Service\EmailVerificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EmailVerificationController extends AbstractController
{
    #[Route('/verify-email/{token}', name: 'app_verify_email')]
    public function verifyEmail(
        string $token,
        EmailVerificationService $emailVerification
    ): Response {
        $utilisateur = $emailVerification->verifyToken($token);

        if (!$utilisateur) {
            $this->addFlash('error', 'Le lien de vérification est invalide ou a expiré.');
            return $this->redirectToRoute('app_login');
        }

        $this->addFlash('success', 'Votre email a été vérifié avec succès ! Vous pouvez maintenant vous connecter.');
        
        return $this->redirectToRoute('app_login');
    }

    #[Route('/resend-verification', name: 'app_resend_verification')]
    public function resendVerification(
        Request $request,
        UtilisateurRepository $utilisateurRepository,
        EmailVerificationService $emailVerification
    ): Response {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $utilisateur = $utilisateurRepository->findOneBy(['email' => $email]);

            if (!$utilisateur) {
                $this->addFlash('error', 'Aucun compte trouvé avec cet email.');
            } elseif ($utilisateur->isVerified()) {
                $this->addFlash('info', 'Votre email est déjà vérifié.');
            } else {
                try {
                    $emailVerification->resendVerificationEmail($utilisateur);
                    $this->addFlash('success', 'Un nouvel email de vérification a été envoyé.');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'envoi de l\'email.');
                }
            }

            return $this->redirectToRoute('app_resend_verification');
        }

        return $this->render('security/resend_verification.html.twig');
    }
}