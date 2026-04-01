<?php

namespace App\Repository;

use App\Entity\ProduitFinancier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProduitFinancier>
 */
class ProduitFinancierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProduitFinancier::class);
    }

    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.typeFinancement = :type')
            ->setParameter('type', $type)
            ->orderBy('p.nomProduit', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.nomProduit', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
