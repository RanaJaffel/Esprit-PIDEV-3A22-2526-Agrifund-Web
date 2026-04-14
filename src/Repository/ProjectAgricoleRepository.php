<?php

namespace App\Repository;

use App\Entity\ProjectAgricole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectAgricole>
 */
class ProjectAgricoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectAgricole::class);
    }

    
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.datesoumission', 'DESC')
            ->getQuery()
            ->getResult();
    }

   
    public function findByNom(string $nom): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.nomproject LIKE :nom')
            ->setParameter('nom', '%' . $nom . '%')
            ->orderBy('p.datesoumission', 'DESC')
            ->getQuery()
            ->getResult();
    }

    
    public function findByStatut(string $statut): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('p.datesoumission', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
