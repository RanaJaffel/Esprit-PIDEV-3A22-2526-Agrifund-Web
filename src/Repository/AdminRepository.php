<?php

namespace App\Repository;

use App\Entity\Admin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Admin>
 */
class AdminRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Admin::class);
    }

    /**
     * Trouve tous les admins avec leurs utilisateurs
     */
    public function findAllWithUsers(): array
    {
        return $this->createQueryBuilder('a')
            ->join('a.utilisateur', 'u')
            ->orderBy('u.dateInscrit', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre d'admins
     */
    public function countAdmins(): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}