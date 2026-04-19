<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Achat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Achat>
 */
class AchatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Achat::class);
    }

    public function findOneByReference(string $reference): ?Achat
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.reference = :reference')
            ->setParameter('reference', $reference)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Achat[]
     */
    public function findByUtilisateur(int $utilisateurId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.utilisateur = :utilisateurId')
            ->setParameter('utilisateurId', $utilisateurId)
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
