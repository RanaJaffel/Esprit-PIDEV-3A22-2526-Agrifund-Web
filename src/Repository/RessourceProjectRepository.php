<?php

namespace App\Repository;

use App\Entity\RessourceProject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class RessourceProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RessourceProject::class);
    }

    
    public function findByProject(int $projectId): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.project = :projectId')
            ->setParameter('projectId', $projectId)
            ->orderBy('r.dateajout', 'DESC')
            ->getQuery()
            ->getResult();
    }

    
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.typeressource = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult();
    }

    
    public function getTotalCostForProject(int $projectId): float
    {
        $result = $this->createQueryBuilder('r')
            ->select('SUM(r.cout * r.quantite) as total')
            ->where('r.project = :projectId')
            ->setParameter('projectId', $projectId)
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }
}
