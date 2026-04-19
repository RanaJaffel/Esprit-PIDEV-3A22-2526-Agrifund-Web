<?php
// src/Repository/FaceRecognitionLogRepository.php

namespace App\Repository;

use App\Entity\FaceRecognitionLog;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FaceRecognitionLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FaceRecognitionLog::class);
    }

    public function save(FaceRecognitionLog $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findRecentAttempts(Utilisateur $user, int $limit = 10): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.utilisateur = :user')
            ->setParameter('user', $user)
            ->orderBy('f.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countFailedAttempts(Utilisateur $user, \DateTimeInterface $since): int
    {
        return $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->andWhere('f.utilisateur = :user')
            ->andWhere('f.success = :success')
            ->andWhere('f.createdAt >= :since')
            ->setParameter('user', $user)
            ->setParameter('success', false)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getSuccessRate(Utilisateur $user): float
    {
        $total = $this->count(['utilisateur' => $user]);
        
        if ($total === 0) {
            return 0.0;
        }

        $successful = $this->count(['utilisateur' => $user, 'success' => true]);

        return ($successful / $total) * 100;
    }
}