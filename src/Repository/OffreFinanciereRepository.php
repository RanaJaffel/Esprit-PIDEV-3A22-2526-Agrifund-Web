<?php

namespace App\Repository;

use App\Entity\OffreFinanciere;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OffreFinanciere>
 */
class OffreFinanciereRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OffreFinanciere::class);
    }

    public function findActiveOffers(): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.statut = :statut')
            ->setParameter('statut', 'Active')
            ->orderBy('o.nomOffre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByProduit($produitId): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.produitFinancier = :produit')
            ->setParameter('produit', $produitId)
            ->orderBy('o.nomOffre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.nomOffre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
