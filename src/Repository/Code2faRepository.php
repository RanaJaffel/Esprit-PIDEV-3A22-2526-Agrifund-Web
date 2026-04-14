<?php
// src/Repository/Code2faRepository.php

namespace App\Repository;

use App\Entity\Code2fa;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class Code2faRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Code2fa::class);
    }

    public function findValidCodeForUser(Utilisateur $utilisateur, string $code): ?Code2fa
    {
        return $this->createQueryBuilder('c')
            ->where('c.utilisateur = :utilisateur')
            ->andWhere('c.code = :code')
            ->andWhere('c.estUtilise = false')
            ->andWhere('c.dateExpiration > :now')
            ->setParameter('utilisateur', $utilisateur)
            ->setParameter('code', $code)
            ->setParameter('now', new \DateTime())
            ->orderBy('c.dateCreation', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function invalidateOldCodes(Utilisateur $utilisateur): void
    {
        $this->createQueryBuilder('c')
            ->update()
            ->set('c.estUtilise', 'true')
            ->where('c.utilisateur = :utilisateur')
            ->andWhere('c.estUtilise = false')
            ->setParameter('utilisateur', $utilisateur)
            ->getQuery()
            ->execute();
    }
}