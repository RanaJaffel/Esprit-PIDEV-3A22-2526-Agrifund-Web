<?php

namespace App\Controller;

use App\Service\HistoriqueService;
use App\Repository\ReleveHebdomadaireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Process\Process;

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
public function exportPdf(
    int $idproject,
    HistoriqueService $historiqueService
): Response {

    $analysis = $historiqueService->getCompleteAnalysis($idproject);

    $html = $this->renderView('pdf/historique_pdf.html.twig', [
        'idproject' => $idproject,
        'analysis' => $analysis
    ]);

    $tmpHtml = sys_get_temp_dir().'/historique_'.$idproject.'.html';
    $tmpPdf  = sys_get_temp_dir().'/historique_'.$idproject.'.pdf';

    file_put_contents($tmpHtml, $html);

    $process = new Process([
        $_ENV['WKHTMLTOPDF_BINARY'],
        $tmpHtml,
        $tmpPdf
    ]);
    $process->run();

    if (!$process->isSuccessful()) {
        return new Response("Erreur génération PDF");
    }

    return new Response(
        file_get_contents($tmpPdf),
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="historique_'.$idproject.'.pdf"'
        ]
    );
}
}