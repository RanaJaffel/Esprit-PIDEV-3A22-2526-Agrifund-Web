<?php

namespace App\Repository;

use App\Entity\ReleveHebdomadaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReleveHebdomadaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReleveHebdomadaire::class);
    }

    public function findByProject(int $idproject, int $limit = 30): array
    {
        return $this->createQueryBuilder('h')
            ->where('h.idproject = :p')
            ->setParameter('p', $idproject)
            ->orderBy('h.dateDebut', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }

    public function findByProjectAndWeekStart(int $idproject, \DateTimeInterface $weekStart): ?ReleveHebdomadaire
    {
        return $this->createQueryBuilder('h')
            ->where('h.idproject = :p')
            ->andWhere('h.dateDebut = :d')
            ->setParameter('p', $idproject)
            ->setParameter('d', $weekStart)
            ->getQuery()->getOneOrNullResult();
    }

    public function findLatestByProject(int $idproject): ?ReleveHebdomadaire
    {
        return $this->createQueryBuilder('h')
            ->where('h.idproject = :p')
            ->setParameter('p', $idproject)
            ->orderBy('h.dateDebut', 'DESC')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    public function findAllByFilters(?int $idproject = null, ?\DateTimeInterface $weekStart = null, int $limit = 500): array
    {
        $qb = $this->createQueryBuilder('h')
            ->orderBy('h.dateDebut', 'DESC')
            ->setMaxResults($limit);

        if ($idproject !== null) {
            $qb->andWhere('h.idproject = :p')->setParameter('p', $idproject);
        }

        if ($weekStart !== null) {
            $qb->andWhere('h.dateDebut = :d')->setParameter('d', $weekStart);
        }

        return $qb->getQuery()->getResult();
    }
   
}