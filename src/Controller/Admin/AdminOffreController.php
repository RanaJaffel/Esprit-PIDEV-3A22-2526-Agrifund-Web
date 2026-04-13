<?php

namespace App\Controller\Admin;

use App\Entity\OffreFinanciere;
use App\Form\OffreFinanciereType;
use App\Repository\OffreFinanciereRepository;
use App\Repository\ProduitFinancierRepository;
use App\Service\OfferService;
use App\Service\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/offres')]
#[IsGranted('ROLE_ADMIN')]
class AdminOffreController extends AbstractController
{
    public function __construct(
        private OfferService $offerService,
        private PdfService $pdfService
    ) {}

    #[Route('/', name: 'admin_offre_index', methods: ['GET'])]
    public function index(
        Request $request,
        OffreFinanciereRepository $repository,
        ProduitFinancierRepository $produitRepository,
        PaginatorInterface $paginator
    ): Response {
        // Récupérer les filtres
        $keyword = $request->query->get('q', '');
        $statut = $request->query->get('statut', '');
        $produitId = $request->query->get('produit') !== null && $request->query->get('produit') !== '' ? (int)$request->query->get('produit') : null;
        $typeFinancement = $request->query->get('type', '');
        $sortBy = $request->query->get('sort', 'nomOffre');
        $sortOrder = $request->query->get('order', 'ASC');

        // QueryBuilder avec filtres
        $queryBuilder = $repository->searchAndFilter(
            $keyword ?: null,
            $statut ?: null,
            $produitId,
            $typeFinancement ?: null,
            $sortBy,
            $sortOrder
        );

        // Pagination (sorting disabled in KNP — handled by QueryBuilder)
        $offres = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            10,
            ['defaultSortFieldName' => null, 'sortFieldParameterName' => null]
        );

        // Statistiques
        $dashboardStats = $this->offerService->getDashboardStatistics();

        // Données pour les filtres
        $produits = $produitRepository->findAll();
        $statutsDisponibles = $repository->findDistinctStatuts();

        return $this->render('admin/offre/index.html.twig', [
            'offres' => $offres,
            'keyword' => $keyword,
            'statut' => $statut,
            'produitId' => $produitId,
            'typeFinancement' => $typeFinancement,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'dashboardStats' => $dashboardStats,
            'produits' => $produits,
            'statutsDisponibles' => $statutsDisponibles,
        ]);
    }

    #[Route('/new', name: 'admin_offre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $offre = new OffreFinanciere();
        $form = $this->createForm(OffreFinanciereType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($offre);
            $em->flush();
            $this->addFlash('success', 'Offre financière ajoutée avec succès !');
            return $this->redirectToRoute('admin_offre_index');
        }

        return $this->render('admin/offre/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/export-csv', name: 'admin_offre_export_csv', methods: ['GET'])]
    public function exportCsv(): StreamedResponse
    {
        $data = $this->offerService->getExportData();

        $response = new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            if (!empty($data)) {
                fputcsv($handle, array_keys($data[0]), ';');
            }

            foreach ($data as $row) {
                fputcsv($handle, $row, ';');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="offres_financieres_' . date('Y-m-d') . '.csv"');

        return $response;
    }

    #[Route('/stats', name: 'admin_offre_stats', methods: ['GET'])]
    public function stats(): JsonResponse
    {
        $stats = $this->offerService->getDashboardStatistics();
        return new JsonResponse($stats);
    }

    #[Route('/export-pdf', name: 'admin_offre_export_pdf', methods: ['GET'])]
    public function exportPdf(OffreFinanciereRepository $repository): Response
    {
        $offres = $repository->findAll();
        $stats = $repository->getStatistics();
        $byStatut = $repository->countByStatut();

        return $this->pdfService->generatePdfResponse(
            'pdf/offres.html.twig',
            [
                'offres' => $offres,
                'stats' => $stats,
                'byStatut' => $byStatut,
                'date' => new \DateTime(),
            ],
            'offres_financieres_' . date('Y-m-d') . '.pdf',
            'A4',
            'landscape'
        );
    }

    #[Route('/{id}/changer-statut', name: 'admin_offre_changer_statut', methods: ['POST'])]
    public function changerStatut(OffreFinanciere $offre, Request $request): Response
    {
        $nouveauStatut = $request->request->get('statut');

        if (!$nouveauStatut) {
            $this->addFlash('error', 'Statut non spécifié.');
            return $this->redirectToRoute('admin_offre_index');
        }

        $result = $this->offerService->changerStatut($offre, $nouveauStatut);

        if ($result['success']) {
            $this->addFlash('success', $result['message']);
        } else {
            $this->addFlash('error', $result['message']);
        }

        return $this->redirectToRoute('admin_offre_index');
    }

    #[Route('/{id}/dupliquer', name: 'admin_offre_dupliquer', methods: ['POST'])]
    public function dupliquer(OffreFinanciere $offre, Request $request): Response
    {
        if ($this->isCsrfTokenValid('dupliquer' . $offre->getId(), $request->request->get('_token'))) {
            $nouvelleOffre = $this->offerService->dupliquerOffre($offre);
            $this->addFlash('success', sprintf(
                'Offre dupliquée avec succès ! Nouvelle offre : "%s" (ID: %d)',
                $nouvelleOffre->getNomOffre(),
                $nouvelleOffre->getId()
            ));
        }

        return $this->redirectToRoute('admin_offre_index');
    }

    #[Route('/{id}', name: 'admin_offre_show', methods: ['GET'])]
    public function show(OffreFinanciere $offre): Response
    {
        $resume = $this->offerService->getOffreResume($offre);

        return $this->render('admin/offre/show.html.twig', [
            'offre' => $offre,
            'resume' => $resume,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_offre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, OffreFinanciere $offre, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(OffreFinanciereType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Offre financière mise à jour avec succès !');
            return $this->redirectToRoute('admin_offre_index');
        }

        return $this->render('admin/offre/edit.html.twig', [
            'offre' => $offre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_offre_delete', methods: ['POST'])]
    public function delete(Request $request, OffreFinanciere $offre, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $offre->getId(), $request->request->get('_token'))) {
            $em->remove($offre);
            $em->flush();
            $this->addFlash('success', 'Offre supprimée avec succès !');
        }

        return $this->redirectToRoute('admin_offre_index');
    }
}
