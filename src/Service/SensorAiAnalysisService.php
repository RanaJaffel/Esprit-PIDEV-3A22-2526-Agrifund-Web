<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class SensorAiAnalysisService
{
    public function __construct(
        private EntityManagerInterface $em,
        private IoTAiClient $aiClient,
        private NotificationService $notificationService
    ) {}

    public function analyze(
        int $projectId,
        int $sensorId,
        string $typeMesure,
        float $currentValue
    ): void {

        $conn = $this->em->getConnection();

        $rows = $conn->fetchAllAssociative(
            "SELECT valeur_mesuree
             FROM releve_terrain
             WHERE id_capteur = ?
             ORDER BY date_heure DESC
             LIMIT 50",
            [$sensorId]
        );

        $history = array_map(
            fn($r) => (float)$r['valeur_mesuree'],
            $rows
        );

        $result = $this->aiClient->detectAnomaly(
            $projectId,
            $sensorId,
            $typeMesure,
            $history,
            $currentValue
        );

        if (!$result || empty($result['isAnomaly'])) {
            return;
        }

        $this->notificationService->createOnce(
            $projectId,
            $sensorId,
            'IA_ANOMALY',
            $result['severity'],
            $result['reason']
        );

        $this->em->flush();
    }
}