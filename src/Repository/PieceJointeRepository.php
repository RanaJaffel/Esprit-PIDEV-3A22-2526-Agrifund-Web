<?php

namespace App\Repository;

use App\Entity\PieceJointe;
use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PieceJointe>
 */
class PieceJointeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PieceJointe::class);
    }

    /**
     * Trouve les pièces jointes d'un message
     */
    public function findByMessage(Message $message): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.message = :message')
            ->setParameter('message', $message)
            ->orderBy('p.dateUpload', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les pièces jointes par type
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.typeFichier = :type')
            ->setParameter('type', $type)
            ->orderBy('p.dateUpload', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcule l'espace total utilisé par les pièces jointes
     */
    public function getTotalSize(): int
    {
        $result = $this->createQueryBuilder('p')
            ->select('SUM(p.tailleOctets)')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }
}