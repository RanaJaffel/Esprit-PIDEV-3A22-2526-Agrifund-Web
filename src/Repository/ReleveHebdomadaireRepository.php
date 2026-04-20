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

    // 🔹 1. Historique par projet
    public function findByProject(int $idproject, int $limit = 30): array
    {
        return $this->createQueryBuilder('h')
            ->where('h.idproject = :p')
            ->setParameter('p', $idproject)
            ->orderBy('h.dateDebut', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    // 🔹 2. Dernières semaines
    public function findLastWeeks(int $idproject, int $weeks = 8): array
    {
        return $this->createQueryBuilder('h')
            ->where('h.idproject = :p')
            ->setParameter('p', $idproject)
            ->orderBy('h.dateDebut', 'DESC')
            ->setMaxResults($weeks)
            ->getQuery()
            ->getResult();
    }

    // 🔹 3. Analyse de tendance (✅ CORRIGÉE)
    public function getTrend(int $idproject): array
    {
        $weeks = $this->findLastWeeks($idproject, 4);

        if (count($weeks) < 2) {
            return [
                'trend' => 'INSUFFICIENT_DATA',
                'temp_variation' => 0,
                'hum_variation' => 0,
                'weeks_analyzed' => count($weeks)
            ];
        }

        $latest = $weeks[0];
        $previous = $weeks[1];

        // ✅ CORRECTION ICI
        $tempVariation = $latest->getTempMoyenne() - $previous->getTempMoyenne();
        $humVariation = $latest->getHumiditeMoyenne() - $previous->getHumiditeMoyenne();

        $direction = 'STABLE';

        if (abs($tempVariation) > 3 || abs($humVariation) > 10) {
            $direction = ($tempVariation > 0 || $humVariation < 0)
                ? 'DETERIORATING'
                : 'IMPROVING';
        }

        return [
            'trend' => $direction,
            'temp_variation' => $tempVariation,
            'hum_variation' => $humVariation,
            'weeks_analyzed' => count($weeks)
        ];
    }

    // 🔹 4. Statistiques globales
    public function getGlobalStats(int $idproject): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT 
                AVG(temp_moy) as avg_temp,
                MAX(temp_moy) as max_temp,
                MIN(temp_moy) as min_temp,
                AVG(hum_moy) as avg_hum,
                MAX(hum_moy) as max_hum,
                MIN(hum_moy) as min_hum,
                COUNT(*) as total_weeks
            FROM releve_hebdomadaire
            WHERE idproject = :idproject
            AND date_debut >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
        ';

        return $conn->executeQuery($sql, [
            'idproject' => $idproject
        ])->fetchAssociative();
    }

    // 🔹 5. Distribution des conditions
    public function getConditionDistribution(int $idproject): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT 
                condition_dominante,
                COUNT(*) as count,
                (COUNT(*) * 100.0 / (
                    SELECT COUNT(*) 
                    FROM releve_hebdomadaire 
                    WHERE idproject = :idproject
                )) as percentage
            FROM releve_hebdomadaire
            WHERE idproject = :idproject
            GROUP BY condition_dominante
            ORDER BY count DESC
        ';

        return $conn->executeQuery($sql, [
            'idproject' => $idproject
        ])->fetchAllAssociative();
    }
}