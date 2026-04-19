<?php

namespace App\Repository;

use App\Entity\TokenReinitialisation;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TokenReinitialisationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TokenReinitialisation::class);
    }

    /**
     * Trouve un token valide (non utilisé et non expiré)
     */
    public function findValidToken(string $token): ?TokenReinitialisation
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.token = :token')
            ->andWhere('t.utilise = :utilise')
            ->andWhere('t.dateExpiration > :now')
            ->setParameter('token', $token)
            ->setParameter('utilise', false)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Invalide tous les tokens d'un utilisateur
     */
    public function invalidateUserTokens(Utilisateur $utilisateur): void
    {
        $this->createQueryBuilder('t')
            ->update()
            ->set('t.utilise', ':utilise')
            ->where('t.utilisateur = :utilisateur')
            ->andWhere('t.utilise = :notUtilise')
            ->setParameter('utilise', true)
            ->setParameter('notUtilise', false)
            ->setParameter('utilisateur', $utilisateur)
            ->getQuery()
            ->execute();
    }

    /**
     * Supprime les tokens expirés (pour nettoyage automatique)
     */
    public function deleteExpiredTokens(): int
    {
        return $this->createQueryBuilder('t')
            ->delete()
            ->where('t.dateExpiration < :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->execute();
    }
}