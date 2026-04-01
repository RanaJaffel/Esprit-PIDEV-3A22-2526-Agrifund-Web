<?php

namespace App\Repository;

use App\Entity\Gallery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Gallery>
 */
class GalleryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gallery::class);
    }

    /**
     * Find all active gallery items
     */
    public function findActive(): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('g.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find gallery items with pagination
     */
    public function findActiveWithLimit(int $limit = 10, int $offset = 0): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('g.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
