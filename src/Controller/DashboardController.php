<?php

namespace App\Controller;

use App\Service\IrrigationAIService;
use App\Service\HistoriqueService;
use App\Service\BlockchainService;
use App\Repository\ReleveTerrainRepository;
use App\Repository\ReleveHebdomadaireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard/{idproject}', name: 'app_dashboard')]
    public function index(
        int $idproject,
        IrrigationAIService $irrigationService,
        HistoriqueService $historiqueService,
        BlockchainService $blockchainService,
        ReleveTerrainRepository $mesureRepo,
        ReleveHebdomadaireRepository $releveRepo
    ): Response
    {
        // 🔥 1. Données mesures (TON CODE)
        $dernieresMesures = $mesureRepo->findLast24Hours($idproject);
        
        // 🔥 2. Analyse irrigation IA
        $tempMoy = $this->calculateAverage($dernieresMesures, 'TEMPERATURE');
        $humMoy = $this->calculateAverage($dernieresMesures, 'HUMIDITE_SOL');
        $pluie = $this->calculateSum($dernieresMesures, 'PLUIE');

        $irrigationDecision = $irrigationService->analyzeIrrigation($idproject, [
            'temperature' => $tempMoy,
            'humidite' => $humMoy,
            'pluie' => $pluie
        ]);

        // 🔥 3. Historique comparatif
        $historiqueData = $historiqueService->compareLastWeeks($idproject);

        // 🔥 4. Blockchain status
        $blockchainStatus = [
            'totalBlocks' => $blockchainService->getTotalBlocks(),
            'lastHash' => $blockchainService->getLastHash(),
            'lastBlockDate' => $blockchainService->getLastBlockDate(),
            'isValid' => $blockchainService->verifyChain()
        ];

        // 🔥 5. Autres données (TON CODE)
        $capteurs = []; // À récupérer depuis ton repo
        $rapportsJour = [];
        $releveHebdo = $releveRepo->findOneBy(['projetId' => $idproject], ['createdAt' => 'DESC']);
        $latestNotifs = [];
        $unreadCount = 0;

        return $this->render('dashboard/index.html.twig', [
            'idproject' => $idproject,
            'dernieresMesures' => $dernieresMesures,
            'irrigationDecision' => $irrigationDecision,
            'historiqueData' => $historiqueData,
            'blockchainStatus' => $blockchainStatus,
            'capteurs' => $capteurs,
            'rapportsJour' => $rapportsJour,
            'releveHebdo' => $releveHebdo,
            'latestNotifs' => $latestNotifs,
            'unreadCount' => $unreadCount
        ]);
    }

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