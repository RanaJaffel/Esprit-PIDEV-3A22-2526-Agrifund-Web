<?php

namespace App\Service;

use App\Entity\IrrigationDecision;
use Doctrine\ORM\EntityManagerInterface;

class IrrigationAIService
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    /**
     * Analyse complète (pour la page Twig)
     */
    public function analyzeComplete(int $projetId, array $currentData): array
{
    $analysis = $this->analyzeIrrigation($projetId, $currentData);

    $weatherForecast = $this->getWeatherForecast();

    $recommendation = $this->generateRecommendation(
        $analysis,
        $weatherForecast
    );

    $waterSavings = $this->calculateWaterSavings($analysis['decision']);

    $optimizationTips = $this->getOptimizationTips($analysis); // ✅ AJOUT

    return [
        'analysis' => $analysis,
        'recommendation' => $recommendation,
        'weatherForecast' => $weatherForecast,
        'waterSavings' => $waterSavings,
        'optimizationTips' => $optimizationTips // ✅ AJOUT
    ];
}

    /**
     * Analyse IA principale
     */
    public function analyzeIrrigation(int $projetId, array $data): array
    {
        $temp = $this->sanitizeValue($data['temperature'] ?? 20);
        $humidity = $this->sanitizeValue($data['humidite'] ?? 50);
        $pluie = $this->sanitizeValue($data['pluie'] ?? 0);

        $et0 = $this->calculateEvapotranspiration($temp, $humidity);
        $besoinEau = max(0, $et0 - $pluie);
        $urgenceScore = $this->calculateUrgencyScore($humidity, $besoinEau);
        $decision = $this->makeDecision($urgenceScore);
        $confidence = $this->calculateConfidence($data);

        $this->saveDecision($projetId, $decision, $data, $confidence);

        return [
            'decision' => $decision['action'],
            'quantite' => $decision['quantite'],
            'urgence' => $urgenceScore,
            'confidence' => $confidence,
            'et0' => round($et0, 2),
            'besoin_eau' => round($besoinEau, 2),
            'explication' => $this->getExplanation($decision, $urgenceScore),
            'input_data' => $data
        ];
    }

    /**
     * Recommandation intelligente
     */
    private function generateRecommendation(array $analysis, array $weather): array
    {
        $action = 'SCHEDULE';
        $scheduledDate = new \DateTime('+1 day');
        $reasoning = '';

        if ($analysis['urgence'] >= 70) {
            $action = 'IMMEDIATE';
            $scheduledDate = new \DateTime('now');
            $reasoning = 'Urgence élevée détectée.';
        } elseif (!empty($weather['rain_forecast']) && $weather['rain_forecast'] > 10) {
            $action = 'DELAY';
            $scheduledDate = new \DateTime('+3 days');
            $reasoning = 'Pluie prévue. Irrigation reportée.';
        } elseif ($analysis['urgence'] >= 40) {
            $action = 'SCHEDULE';
            $scheduledDate = new \DateTime('+1 day 06:00');
            $reasoning = 'Irrigation recommandée demain matin.';
        } else {
            $action = 'CANCEL';
            $reasoning = 'Conditions optimales.';
        }

        return [
            'action' => $action,
            'scheduled_date' => $scheduledDate,
            'reasoning' => $reasoning,
            'quantity' => $analysis['quantite']
        ];
    }

    /**
     * Simulation météo
     */
    private function getWeatherForecast(): array
{
    return [
        'temperature_forecast' => 28,
        'rain_forecast' => 5,
        'humidity_forecast' => 60,
        'confidence' => 0.85,
        'next_rain_date' => new \DateTime('+2 days') // ✅ AJOUTÉ
    ];
}

    /**
     * Calcul économie d'eau
     */
    private function calculateWaterSavings(string $decision): array
    {
        $savings = 0;
        $message = '';

        switch ($decision) {
            case 'ATTENDRE':
                $savings = 200;
                $message = '200L économisés grâce à l\'IA';
                break;
            case 'SURVEILLER':
                $savings = 100;
                $message = '100L économisés';
                break;
            case 'IRRIGUER':
                $savings = 0;
                $message = 'Irrigation optimale';
                break;
            case 'IRRIGUER_URGENT':
                $savings = -50;
                $message = 'Surconsommation (urgence)';
                break;
        }

        return [
            'liters' => $savings,
            'message' => $message
        ];
    }

    /**
     * Sauvegarde en base
     */
    private function saveDecision(
        int $projetId,
        array $decision,
        array $data,
        float $confidence
    ): void {
        $record = new IrrigationDecision();
        $record->setProjetId($projetId);
        $record->setDecision($decision['action']);
        $record->setQuantite($decision['quantite']);
        $record->setConfidence($confidence);

        $record->setInputData([
            'terrain_data' => $data,
            'generated_at' => date('Y-m-d H:i:s')
        ]);

        $this->em->persist($record);
        $this->em->flush();
    }

    // ===============================
    // IA INTERNE
    // ===============================

    private function calculateEvapotranspiration(float $temp, float $humidity): float
    {
        return max(0, (0.15 * $temp) - (0.05 * $humidity));
    }

    private function calculateUrgencyScore(float $humidity, float $besoinEau): int
    {
        $score = 0;

        if ($humidity < 20) $score += 50;
        elseif ($humidity < 40) $score += 30;
        elseif ($humidity < 60) $score += 10;

        if ($besoinEau > 10) $score += 40;
        elseif ($besoinEau > 5) $score += 20;

        return min(100, $score);
    }

    private function makeDecision(int $urgenceScore): array
    {
        if ($urgenceScore >= 70)
            return ['action' => 'IRRIGUER_URGENT', 'quantite' => '30L/m²'];

        if ($urgenceScore >= 40)
            return ['action' => 'IRRIGUER', 'quantite' => '20L/m²'];

        if ($urgenceScore >= 20)
            return ['action' => 'SURVEILLER', 'quantite' => '10L/m²'];

        return ['action' => 'ATTENDRE', 'quantite' => '0'];
    }

    private function calculateConfidence(array $data): float
    {
        $confidence = 0.5;

        if (isset($data['temperature'])) $confidence += 0.2;
        if (isset($data['humidite'])) $confidence += 0.2;
        if (isset($data['pluie'])) $confidence += 0.1;

        return min(1.0, $confidence);
    }

    private function getExplanation(array $decision, int $urgence): string
    {
        return match ($decision['action']) {
            'IRRIGUER_URGENT' => "🔴 Action immédiate requise (urgence: {$urgence}%)",
            'IRRIGUER' => "🟡 Irrigation recommandée (urgence: {$urgence}%)",
            'SURVEILLER' => "🟢 Surveillance active conseillée",
            default => "✅ Conditions optimales"
        };
    }

    /**
     * Évite valeurs absurdes (ex: 999°C)
     */
    private function sanitizeValue(float $value): float
    {
        if ($value > 100) return 100;
        if ($value < -50) return -50;
        return $value;
    }
    private function getOptimizationTips(array $analysis): array
{
    $tips = [];

    if ($analysis['urgence'] > 60) {
        $tips[] = '💧 Installer un système goutte-à-goutte.';
    }

    if ($analysis['et0'] > 8) {
        $tips[] = '🌿 Ajouter du paillage pour réduire l’évaporation.';
    }

    if ($analysis['confidence'] < 0.7) {
        $tips[] = '📡 Ajouter plus de capteurs pour améliorer la précision.';
    }

    $tips[] = '⏰ Irrigation idéale : 6h-8h ou 18h-20h.';

    return $tips;
}
}