<?php
// src/Repository/VerificationTokenRepository.php

namespace App\Repository;

use App\Entity\VerificationToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class VerificationTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VerificationToken::class);
    }

    public function findValidToken(string $token): ?VerificationToken
    {
        return $this->createQueryBuilder('vt')
            ->where('vt.token = :token')
            ->andWhere('vt.expiresAt > :now')
            ->setParameter('token', $token)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function deleteExpiredTokens(): int
    {
        return $this->createQueryBuilder('vt')
            ->delete()
            ->where('vt.expiresAt < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->execute();
    }
}