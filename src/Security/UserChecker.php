<?php
// src/Security/UserChecker.php

namespace App\Security;

use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof Utilisateur) {
            return;
        }

        // Vérifier si l'email est vérifié
        if (!$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException(
                'Votre email n\'a pas encore été vérifié. Veuillez consulter votre boîte de réception et cliquer sur le lien de vérification.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof Utilisateur) {
            return;
        }

        // Vérifications supplémentaires post-authentification si nécessaire
        // Par exemple, vérifier si le compte agriculteur ou banque est approuvé
    }
}