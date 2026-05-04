<?php
// src/EventSubscriber/TwoFactorSubscriber.php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class TwoFactorSubscriber implements EventSubscriberInterface
{
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
        
        if (is_string($currentRoute) && in_array($currentRoute, $publicRoutes, true)) {
            return;
        }

        // Si l'utilisateur a la 2FA activée mais n'a pas vérifié son code
        if ($session->get('2fa_pending_password_login') && !$session->get('2fa_verified')) {
            // Rediriger vers la page de vérification 2FA
            $event->setResponse(new RedirectResponse('/login/2fa'));
        }
    }
}
