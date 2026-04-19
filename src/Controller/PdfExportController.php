<?php

namespace App\Controller;

use App\Repository\CapteurRepository;
use App\Repository\ReleveTerrainRepository;
use App\Repository\RapportJournalierRepository;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/pdf')]
class PdfExportController extends AbstractController
{
    private function denyIfProjectNotOwned(int $idproject, CapteurRepository $capteurRepo): void
    {
        if ($this->isGranted('ROLE_ADMIN')) return;

        $user = $this->getUser();
        if (!$user || !method_exists($user, 'getId')) {
            throw $this->createAccessDeniedException("Vous devez être connecté.");
        }

        $count = $capteurRepo->count([
            'idproject' => $idproject,
            'idUser' => (int) $user->getId(),
        ]);

        if ($count === 0) {
            throw $this->createAccessDeniedException("Accès refusé : projet non autorisé.");
        }
    }

    // ==========================
    // PDF Rapport journalier
    // ==========================
    #[Route('/projets/{idproject}/rapport-journalier/{date}', name: 'pdf_rapport_journalier', methods: ['GET'])]
    public function rapportJournalierPdf(
        int $idproject,
        string $date,
        CapteurRepository $capteurRepo,
        RapportJournalierRepository $rapportRepo,
        Pdf $snappy
    ): Response {
        $this->denyIfProjectNotOwned($idproject, $capteurRepo);

        $dt = \DateTime::createFromFormat('Y-m-d', $date);
        if (!$dt) {
            throw $this->createNotFoundException("Date invalide. Format attendu: YYYY-MM-DD");
        }

        $rapports = $rapportRepo->findByProjectAndDate($idproject, $dt);

        $html = $this->renderView('pdf/rapport_journalier.html.twig', [
            'idproject' => $idproject,
            'date' => $dt,
            'rapports' => $rapports,
            'generatedAt' => new \DateTimeImmutable(),
        ]);

        $fileName = sprintf('rapport_journalier_projet_%d_%s.pdf', $idproject, $dt->format('Ymd'));

        return new Response(
            $snappy->getOutputFromHtml($html, [
                'margin-top' => 10,
                'margin-bottom' => 10,
                'margin-left' => 10,
                'margin-right' => 10,
            ]),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$fileName.'"',
            ]
        );
    }

    // ==========================
    // PDF Relevés terrain (bruts)
    // ==========================
    #[Route('/projets/{idproject}/releves-terrain', name: 'pdf_releves_terrain', methods: ['GET'])]
    public function relevesTerrainPdf(
        int $idproject,
        Request $request,
        CapteurRepository $capteurRepo,
        ReleveTerrainRepository $releveRepo,
        Pdf $snappy
    ): Response {
        $this->denyIfProjectNotOwned($idproject, $capteurRepo);

        // filtres
        $fromStr = (string) $request->query->get('from', (new \DateTime('-1 day'))->format('Y-m-d'));
        $toStr   = (string) $request->query->get('to', (new \DateTime())->format('Y-m-d'));
        $type    = (string) $request->query->get('type', '');
        $qualite = (string) $request->query->get('qualite', '');

        $from = \DateTimeImmutable::createFromFormat('Y-m-d', $fromStr) ?: new \DateTimeImmutable('-1 day');
        $to   = \DateTimeImmutable::createFromFormat('Y-m-d', $toStr) ?: new \DateTimeImmutable('now');

        // inclure toute la journée "to"
        $to = $to->setTime(23, 59, 59);

        $mesures = $releveRepo->findByProjectFilters($idproject, $from, $to, $type, $qualite);

        $html = $this->renderView('pdf/releves_terrain.html.twig', [
            'idproject' => $idproject,
            'from' => $from,
            'to' => $to,
            'type' => $type,
            'qualite' => $qualite,
            'mesures' => $mesures,
            'generatedAt' => new \DateTimeImmutable(),
        ]);

        $fileName = sprintf('releves_terrain_projet_%d_%s_%s.pdf', $idproject, $from->format('Ymd'), $to->format('Ymd'));

        return new Response(
            $snappy->getOutputFromHtml($html, [
                'margin-top' => 10,
                'margin-bottom' => 10,
                'margin-left' => 10,
                'margin-right' => 10,
                'orientation' => 'Landscape', // tableau large
            ]),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$fileName.'"',
            ]
        );
    }
}