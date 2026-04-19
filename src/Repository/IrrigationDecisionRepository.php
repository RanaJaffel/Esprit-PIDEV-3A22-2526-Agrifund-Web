<?php

namespace App\Repository;

use App\Entity\IrrigationDecision;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class IrrigationDecisionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IrrigationDecision::class);
    }

    /**
     * Récupère les 10 dernières décisions pour un projet
     */
    public function findLastDecisions(int $projetId, int $limit = 10): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.projetId = :projetId')
            ->setParameter('projetId', $projetId)
            ->orderBy('i.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques des décisions (30 derniers jours)
     */
    public function getDecisionStats(int $projetId): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT 
                decision,
                COUNT(*) as total,
                AVG(confidence) as avg_confidence
            FROM irrigation_decisions
            WHERE projet_id = :projetId
            AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY decision
        ';

        return $conn->executeQuery($sql, ['projetId' => $projetId])->fetchAllAssociative();
    }

    /**
     * Économie d'eau estimée (en litres)
     */
    public function getWaterSavings(int $projetId): float
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT 
                SUM(
                    CASE 
                        WHEN decision = "ATTENDRE" THEN 200
                        WHEN decision = "SURVEILLER" THEN 100
                        ELSE 0
                    END
                ) as savings
            FROM irrigation_decisions
            WHERE projet_id = :projetId
            AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ';

        $result = $conn->executeQuery($sql, ['projetId' => $projetId])->fetchOne();
        return (float) ($result ?? 0);
    }
}