<?php

namespace App\Repository;

use App\Entity\Capteur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CapteurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Capteur::class);
    }

    // ✅ Trouver capteurs par projet
    public function findByProject(int $idproject): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.idproject = :idproject')
            ->setParameter('idproject', $idproject)
            ->orderBy('c.dateInstallation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // ✅ Trouver capteurs actifs par projet
    public function findActifsByProject(int $idproject): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.idproject = :idproject')
            ->andWhere('c.statut = :statut')
            ->setParameter('idproject', $idproject)
            ->setParameter('statut', 'ACTIF')
            ->getQuery()
            ->getResult();
    }

    // ✅ Trouver capteurs par utilisateur
    public function findByUser(int $idUser): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.idUser = :idUser')
            ->setParameter('idUser', $idUser)
            ->orderBy('c.dateInstallation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // ✅ Compter capteurs actifs par projet
    public function countActifsByProject(int $idproject): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.idCapteur)')
            ->where('c.idproject = :idproject')
            ->andWhere('c.statut = :statut')
            ->setParameter('idproject', $idproject)
            ->setParameter('statut', 'ACTIF')
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function findDistinctProjectsByUser(int $userId): array
{
    $rows = $this->createQueryBuilder('c')
        ->select('DISTINCT c.idproject AS idproject')
        ->where('c.idUser = :uid')
        ->andWhere('c.idproject IS NOT NULL')
        ->setParameter('uid', $userId)
        ->orderBy('c.idproject', 'ASC')
        ->getQuery()
        ->getArrayResult();

    return array_map(fn($r) => (int) $r['idproject'], $rows);
}
}