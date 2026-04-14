<?php
// src/EventSubscriber/TwoFactorSubscriber.php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TwoFactorSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TokenStorageInterface $tokenStorage
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 9],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();
        
        // Routes qui ne nécessitent pas de vérification 2FA
        $publicRoutes = ['app_login', 'app_logout', 'app_login_2fa', 'app_login_2fa_resend', 'app_register'];
        $currentRoute = $request->attributes->get('_route');
        
        if (in_array($currentRoute, $publicRoutes)) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (!$token || !$token->getUser()) {
            return;
        }

        $user = $token->getUser();
        
        // Si l'utilisateur a la 2FA activée mais n'a pas vérifié son code
        if ($user->has2FAEnabled() && !$session->get('2fa_verified')) {
            // Rediriger vers la page de vérification 2FA
            $event->setResponse(new \Symfony\Component\HttpFoundation\RedirectResponse('/login/2fa'));
        }
    }
}