<?php

namespace App\Controller;

use App\Service\HistoriqueService;
use App\Repository\ReleveHebdomadaireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/historique')]
class HistoriqueController extends AbstractController
{
    #[Route('/comparison/{idproject}', name: 'historique_compare')]
    public function comparison(
        int $idproject,
        HistoriqueService $historiqueService,
        ReleveHebdomadaireRepository $releveRepo
    ): Response
    {
        // Analyse complète
        $analysis = $historiqueService->getCompleteAnalysis($idproject);

        // Dernières semaines (pour tableau détaillé)
        $lastWeeks = $releveRepo->findLastWeeks($idproject, 12);

        return $this->render('historique/comparison.html.twig', [
            'idproject' => $idproject,
            'analysis' => $analysis,
            'lastWeeks' => $lastWeeks
        ]);
    }

    #[Route('/export-pdf/{idproject}', name: 'historique_export_pdf')]
    public function exportPdf(int $idproject, HistoriqueService $historiqueService): Response
    {
        // TODO: Implémenter export PDF
        return new Response('Export PDF - En développement');
    }
}