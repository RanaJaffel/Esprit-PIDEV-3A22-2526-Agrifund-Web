<?php

namespace App\Service;

use App\Repository\ReleveHebdomadaireRepository;
use App\Repository\ReleveTerrainRepository;
use Doctrine\ORM\EntityManagerInterface;

class HistoriqueService
{
    public function __construct(
        private ReleveHebdomadaireRepository $releveRepo,
        private ReleveTerrainRepository $releveTerrainRepo,
        private EntityManagerInterface $em
    ) {}

    public function getCompleteAnalysis(int $projetId): array
{
    return [
        'comparison' => $this->compareLastWeeks($projetId),
        'trend' => $this->releveRepo->getTrend($projetId),
        'stats' => $this->getGlobalStats($projetId),
        'conditionDistribution' => $this->releveRepo->getConditionDistribution($projetId),
        'chartData' => $this->getChartData($projetId),
        'prediction' => $this->predictNextWeek($projetId)
    ];
}

    public function compareLastWeeks(int $projetId): array
    {
        $releves = $this->releveRepo->findBy(
            ['idproject' => $projetId],
            ['dateFin' => 'DESC'],
            2
        );

        if (count($releves) < 2) {
            return [
                'error' => 'INSUFFICIENT_DATA',
                'message' => 'Au moins 2 semaines nécessaires'
            ];
        }

        [$current, $previous] = $releves;

        $tempVariation = $current->getTempMoyenne() - $previous->getTempMoyenne();
        $humVariation = $current->getHumiditeMoyenne() - $previous->getHumiditeMoyenne();

        return [
            'current' => [
                'temperature' => $current->getTempMoyenne(),
                'humidite' => $current->getHumiditeMoyenne(),
                'trend' => $current->getConditionDominante(),
                'nb_mesures' => $current->getNbMesuresTotal(),
            ],
            'previous' => [
                'temperature' => $previous->getTempMoyenne(),
                'humidite' => $previous->getHumiditeMoyenne(),
                'trend' => $previous->getConditionDominante(),
                'nb_mesures' => $previous->getNbMesuresTotal(),
            ],
            'variation' => [
                'temperature' => [
                    'valeur' => round($tempVariation, 2),
                    'direction' => $tempVariation > 0 ? 'hausse' : 'baisse'
                ],
                'humidite' => [
                    'valeur' => round($humVariation, 2),
                    'direction' => $humVariation > 0 ? 'hausse' : 'baisse'
                ]
            ],
            'analyse' => $this->analyzeVariation($tempVariation, $humVariation),
            'severity' => $this->calculateSeverity($tempVariation, $humVariation)
        ];
    }

    private function getChartData(int $projetId): array
    {
        $weeks = $this->releveRepo->findLastWeeks($projetId, 8);

        $labels = [];
        $tempData = [];
        $humData = [];

        foreach (array_reverse($weeks) as $week) {
            $labels[] = $week->getDateDebut()->format('d/m');
            $tempData[] = $week->getTempMoyenne();
            $humData[] = $week->getHumiditeMoyenne();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Température (°C)',
                    'data' => $tempData,
                ],
                [
                    'label' => 'Humidité (%)',
                    'data' => $humData,
                ]
            ]
        ];
    }

    private function predictNextWeek(int $projetId): array
{
    $weeks = $this->releveRepo->findLastWeeks($projetId, 4);

    if (count($weeks) < 3) {
        return [
            'available' => false,
            'temperature' => 0,
            'humidite' => 0
        ];
    }

    $count = count($weeks);

    $avgTemp = array_sum(array_map(fn($w) => $w->getTempMoyenne(), $weeks)) / $count;
    $avgHum = array_sum(array_map(fn($w) => $w->getHumiditeMoyenne(), $weeks)) / $count;

    return [
        'available' => true,
        'temperature' => round($avgTemp, 1),
        'humidite' => round($avgHum, 1),
    ];
}

    private function calculateSeverity(float $tempVar, float $humVar): string
    {
        $score = abs($tempVar) + abs($humVar);

        return match (true) {
            $score > 20 => 'CRITICAL',
            $score > 10 => 'HIGH',
            $score > 5 => 'MEDIUM',
            default => 'LOW'
        };
    }

    private function analyzeVariation(float $tempVar, float $humVar): string
    {
        if ($tempVar > 5 && $humVar < -10) return "Stress hydrique";
        if ($tempVar > 8) return "Canicule";
        if ($tempVar < -5) return "Refroidissement";
        if ($humVar > 20) return "Humidité élevée";
        if ($humVar < -15) return "Sécheresse";
        return "Conditions normales";
    }

    /**
     * ✅ FIX FINAL ICI (IMPORTANT)
     */
    public function getGlobalStats(int $projetId): array
    {
        $conn = $this->em->getConnection();

        $sql = "
            SELECT 
                AVG(temp_moyenne) as avg_temp,
                MAX(temp_moyenne) as max_temp,
                MIN(temp_moyenne) as min_temp,
                AVG(humidite_moyenne) as avg_hum,
                MAX(humidite_moyenne) as max_hum,
                MIN(humidite_moyenne) as min_hum,
                COUNT(*) as total_weeks
            FROM releve_hebdomadaire
            WHERE idproject = :id
            AND date_debut >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
        ";

        return $conn->executeQuery($sql, [
            'id' => $projetId
        ])->fetchAssociative();
    }
}