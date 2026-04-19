<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function existsSimilarSince(int $projetId, ?int $capteurId, string $type, \DateTimeImmutable $since): bool
    {
        $qb = $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->andWhere('n.projetId = :pid')
            ->andWhere('n.type = :type')
            ->andWhere('n.createdAt >= :since')
            ->setParameter('pid', $projetId)
            ->setParameter('type', $type)
            ->setParameter('since', $since);

        if ($capteurId === null) {
            $qb->andWhere('n.capteurId IS NULL');
        } else {
            $qb->andWhere('n.capteurId = :cid')->setParameter('cid', $capteurId);
        }

        return (int)$qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function findLatestByProjet(int $projetId, int $limit = 8): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.projetId = :pid')
            ->setParameter('pid', $projetId)
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }

    public function findUnreadByProjet(int $projetId, int $limit = 8): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.projetId = :pid')
            ->andWhere('n.isRead = false')
            ->setParameter('pid', $projetId)
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }

    public function countUnreadByProjet(int $projetId): int
    {
        return (int)$this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->andWhere('n.projetId = :pid')
            ->andWhere('n.isRead = false')
            ->setParameter('pid', $projetId)
            ->getQuery()->getSingleScalarResult();
    }
}