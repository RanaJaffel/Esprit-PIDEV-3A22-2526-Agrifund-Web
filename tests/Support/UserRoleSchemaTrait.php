<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Entity\Admin;
use App\Entity\Agriculteur;
use App\Entity\Banque;
use App\Entity\Parametres2fa;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

trait UserRoleSchemaTrait
{
    private function resetUserRoleSchema(EntityManagerInterface $entityManager): void
    {
        $metadata = [
            $entityManager->getClassMetadata(Utilisateur::class),
            $entityManager->getClassMetadata(Admin::class),
            $entityManager->getClassMetadata(Agriculteur::class),
            $entityManager->getClassMetadata(Banque::class),
            $entityManager->getClassMetadata(Parametres2fa::class),
        ];

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }
}
