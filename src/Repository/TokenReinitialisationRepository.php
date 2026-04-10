<?php

namespace App\Repository;

use App\Entity\TokenReinitialisation;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TokenReinitialisation>
 */
class TokenReinitialisationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TokenReinitialisation::class);
    }

    /**
     * Trouve un token valide
     */
    public function findValidToken(string $token): ?TokenReinitialisation
    {
        return $this->createQueryBuilder('t')
            ->where('t.token = :token')
            ->andWhere('t.utilise = :used')
            ->andWhere('t.dateExpiration > :now')
            ->setParameter('token', $token)
            ->setParameter('used', false)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Invalide tous les anciens tokens d'un utilisateur
     */
    public function invalidateUserTokens(Utilisateur $utilisateur): void
    {
        $this->createQueryBuilder('t')
            ->update()
            ->set('t.utilise', ':used')
            ->where('t.utilisateur = :utilisateur')
            ->andWhere('t.utilise = :notUsed')
            ->setParameter('used', true)
            ->setParameter('utilisateur', $utilisateur)
            ->setParameter('notUsed', false)
            ->getQuery()
            ->execute();
    }

    /**
     * Supprime les tokens expirés
     */
    public function deleteExpiredTokens(): int
    {
        return $this->createQueryBuilder('t')
            ->delete()
            ->where('t.dateExpiration < :now')
            ->orWhere('t.utilise = :used')
            ->setParameter('now', new \DateTime())
            ->setParameter('used', true)
            ->getQuery()
            ->execute();
    }
}