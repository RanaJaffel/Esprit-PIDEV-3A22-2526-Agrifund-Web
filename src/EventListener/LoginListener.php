<?php

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

#[AsEventListener(event: LoginSuccessEvent::class, method: 'onLoginSuccess')]
#[AsEventListener(event: LogoutEvent::class, method: 'onLogout')]
class LoginListener
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        $user->setDerniereConnexion(new \DateTime());
        $user->setEstEnLigne(true);
        $this->entityManager->flush();
    }

    public function onLogout(LogoutEvent $event): void
    {
        $token = $event->getToken();
        if ($token && $token->getUser()) {
            $user = $token->getUser();
            $user->setEstEnLigne(false);
            $this->entityManager->flush();
        }
    }
}