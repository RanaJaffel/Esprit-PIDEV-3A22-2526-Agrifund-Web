<?php

namespace App\Repository;

use App\Entity\Offer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Offer>
 */
class OfferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offer::class);
    }

    /**
     * Find all active offers
     */
    public function findActive(): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.isActive = :active')
            ->andWhere('o.endDate >= :today')
            ->setParameter('active', true)
            ->setParameter('today', new \DateTime())
            ->orderBy('o.endDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find active offers with limit
     */
    public function findActiveWithLimit(int $limit = 5): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.isActive = :active')
            ->andWhere('o.endDate >= :today')
            ->setParameter('active', true)
            ->setParameter('today', new \DateTime())
            ->orderBy('o.endDate', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find offer by slug
     */
    public function findBySlug(string $slug): ?Offer
    {
        return $this->createQueryBuilder('o')
            ->where('o.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
