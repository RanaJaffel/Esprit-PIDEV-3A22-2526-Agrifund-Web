<?php
// src/EventListener/LoginListener.php

namespace App\EventListener;

use App\Service\TwoFactorAuthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Psr\Log\LoggerInterface;

#[AsEventListener(event: LoginSuccessEvent::class, method: 'onLoginSuccess')]
#[AsEventListener(event: LogoutEvent::class, method: 'onLogout')]
class LoginListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UrlGeneratorInterface $urlGenerator,
        private TwoFactorAuthService $twoFactorService,
        private LoggerInterface $logger,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        
        // Vérifier si l'utilisateur a activé la 2FA
        if ($user->has2FAEnabled()) {
            // Ne pas mettre à jour derniereConnexion et estEnLigne pour l'instant
            // Car l'utilisateur n'est pas encore complètement authentifié
            
            try {
                // Générer et envoyer un code de vérification
                $this->twoFactorService->creerEtEnvoyerCode($user);
                
                $this->logger->info('Code 2FA envoyé pour l\'utilisateur : ' . $user->getEmail());
                
                // Rediriger vers la page de vérification 2FA
                $response = new RedirectResponse(
                    $this->urlGenerator->generate('app_login_2fa')
                );
                
                $event->setResponse($response);
                
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de l\'envoi du code 2FA : ' . $e->getMessage(), [
                    'user_id' => $user->getId(),
                    'email' => $user->getEmail(),
                ]);

                // Eviter une erreur 500: on annule l'authentification et on renvoie vers login.
                $this->tokenStorage->setToken(null);

                $request = $event->getRequest();
                if ($request->hasSession()) {
                    $request->getSession()->getFlashBag()->add('error', 'Impossible d\'envoyer le code de vérification. Veuillez réessayer.');
                }

                $event->setResponse(new RedirectResponse(
                    $this->urlGenerator->generate('app_login')
                ));

                return;
            }
        } else {
            // Pas de 2FA : connexion normale
            $user->setDerniereConnexion(new \DateTime());
            $user->setEstEnLigne(true);
            $this->entityManager->flush();
            
            $this->logger->info('Connexion réussie pour l\'utilisateur : ' . $user->getEmail());
        }
    }

    public function onLogout(LogoutEvent $event): void
    {
        $token = $event->getToken();
        if ($token && $token->getUser()) {
            $user = $token->getUser();
            $user->setEstEnLigne(false);
            $this->entityManager->flush();
            
            // Nettoyer la session 2FA
            $request = $event->getRequest();
            if ($request->hasSession()) {
                $session = $request->getSession();
                $session->remove('2fa_verified');
            }
            
            $this->logger->info('Déconnexion de l\'utilisateur : ' . $user->getEmail());
        }
    }
}