<?php

namespace App\Controller;

use App\Service\IrrigationAIService;
use App\Entity\IrrigationDecision; // ✅ AJOUTE CETTE LIGNE
use App\Repository\IrrigationDecisionRepository;
use App\Repository\ReleveTerrainRepository; // ✅ AJOUTE CETTE LIGNE
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/irrigation')]
class IrrigationController extends AbstractController
{
    #[Route('/decision/{idproject}', name: 'irrigation_decision')]
    public function decision(
        int $idproject,
        IrrigationAIService $irrigationService,
        IrrigationDecisionRepository $decisionRepo,
        ReleveTerrainRepository $mesureRepo
    ): Response
    {
        // 1. Données actuelles terrain
        $mesuresRecentes = $mesureRepo->findLast24Hours($idproject);

        $currentData = [
            'temperature' => $this->calculateAverage($mesuresRecentes, 'TEMPERATURE'),
            'humidite' => $this->calculateAverage($mesuresRecentes, 'HUMIDITE_SOL'),
            'pluie' => $this->calculateSum($mesuresRecentes, 'PLUIE')
        ];

        // 2. Analyse complète
        $analysis = $irrigationService->analyzeComplete($idproject, $currentData);

        // 3. Historique décisions
        $historiqueDecisions = $decisionRepo->findLastDecisions($idproject, 10);

        // 4. Statistiques
        $stats = $decisionRepo->getDecisionStats($idproject);
        $waterSavings = $decisionRepo->getWaterSavings($idproject);

        return $this->render('irrigation/decision.html.twig', [
            'idproject' => $idproject,
            'analysis' => $analysis,
            'currentData' => $currentData,
            'historiqueDecisions' => $historiqueDecisions,
            'stats' => $stats,
            'totalWaterSavings' => $waterSavings
        ]);
    }

    #[Route('/historique/{idproject}', name: 'irrigation_historique')]
    public function historique(
        int $idproject,
        IrrigationDecisionRepository $decisionRepo
    ): Response
    {
        $decisions = $decisionRepo->findLastDecisions($idproject, 30);
        $stats = $decisionRepo->getDecisionStats($idproject);

        return $this->render('irrigation/historique.html.twig', [
            'idproject' => $idproject,
            'decisions' => $decisions,
            'stats' => $stats
        ]);
    }

    // Méthodes utilitaires
    private function calculateAverage(array $mesures, string $type): float
    {
        $filtered = array_filter($mesures, fn($m) => $m->getTypeMesure() === $type);
        if (empty($filtered)) return 0;
        $sum = array_reduce($filtered, fn($carry, $m) => $carry + $m->getValeurMesuree(), 0);
        return round($sum / count($filtered), 2);
    }

    private function calculateSum(array $mesures, string $type): float
    {
        $filtered = array_filter($mesures, fn($m) => $m->getTypeMesure() === $type);
        return array_reduce($filtered, fn($carry, $m) => $carry + $m->getValeurMesuree(), 0);
    }
}