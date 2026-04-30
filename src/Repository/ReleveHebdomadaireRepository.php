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

    // 🔹 2. Par semaine spécifique
    public function findByProjectAndWeekStart(int $idproject, \DateTimeInterface $weekStart): ?ReleveHebdomadaire
    {
        return $this->createQueryBuilder('h')
            ->where('h.idproject = :p')
            ->andWhere('h.dateDebut = :d')
            ->setParameter('p', $idproject)
            ->setParameter('d', $weekStart)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // 🔹 3. Dernier relevé
    public function findLatestByProject(int $idproject): ?ReleveHebdomadaire
    {
        return $this->createQueryBuilder('h')
            ->where('h.idproject = :p')
            ->setParameter('p', $idproject)
            ->orderBy('h.dateDebut', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // 🔹 4. Filtres dynamiques
    public function findAllByFilters(?int $idproject = null, ?\DateTimeInterface $weekStart = null, int $limit = 500): array
    {
        $qb = $this->createQueryBuilder('h')
            ->orderBy('h.dateDebut', 'DESC')
            ->setMaxResults($limit);

        if ($idproject !== null) {
            $qb->andWhere('h.idproject = :p')
               ->setParameter('p', $idproject);
        }

        if ($weekStart !== null) {
            $qb->andWhere('h.dateDebut = :d')
               ->setParameter('d', $weekStart);
        }

        return $qb->getQuery()->getResult();
    }

    // 🔹 5. Dernières semaines (utile pour IA)
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

    // 🔹 6. Analyse de tendance
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

        $tempVariation = $latest->getTempMoy() - $previous->getTempMoy();
        $humVariation = $latest->getHumMoy() - $previous->getHumMoy();

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

    // 🔹 7. Statistiques globales (3 derniers mois)
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

    // 🔹 8. Distribution des conditions
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