<?php

namespace App\Controller\Admin;

use App\Entity\ProduitFinancier;
use App\Form\ProduitFinancierType;
use App\Repository\ProduitFinancierRepository;
use App\Service\PdfService;
use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/produits')]
#[IsGranted('ROLE_ADMIN')]
class AdminProduitController extends AbstractController
{
    public function __construct(
        private ProductService $productService,
        private PdfService $pdfService
    ) {}

    #[Route('/', name: 'admin_produit_index', methods: ['GET'])]
    public function index(
        Request $request,
        ProduitFinancierRepository $repository,
        PaginatorInterface $paginator
    ): Response {
        // Récupérer les filtres de la requête
        $keyword = $request->query->get('q', '');
        $typeFinancement = $request->query->get('type', '');
        $tauxMin = $request->query->get('taux_min') !== null && $request->query->get('taux_min') !== '' ? (float)$request->query->get('taux_min') : null;
        $tauxMax = $request->query->get('taux_max') !== null && $request->query->get('taux_max') !== '' ? (float)$request->query->get('taux_max') : null;
        $montant = $request->query->get('montant') !== null && $request->query->get('montant') !== '' ? (float)$request->query->get('montant') : null;
        $sortBy = $request->query->get('sort', 'nomProduit');
        $sortOrder = $request->query->get('order', 'ASC');

        // QueryBuilder avec filtres
        $queryBuilder = $repository->searchAndFilter(
            $keyword ?: null,
            $typeFinancement ?: null,
            $tauxMin,
            $tauxMax,
            null,
            $montant,
            $sortBy,
            $sortOrder
        );

        // Pagination (sorting disabled in KNP — handled by QueryBuilder)
        $produits = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            9,
            ['defaultSortFieldName' => null, 'sortFieldParameterName' => null]
        );
        $productIds = [];
        foreach ($produits as $produit) {
            if ($produit instanceof ProduitFinancier && $produit->getId() !== null) {
                $productIds[] = $produit->getId();
            }
        }
        $offresCounts = $repository->countOffersByProductIds($productIds);

        // Statistiques
        $dashboardStats = $this->productService->getDashboardStatistics();

        // Types disponibles pour le filtre
        $typesDisponibles = $repository->findDistinctTypes();

        return $this->render('admin/produit/index.html.twig', [
            'produits' => $produits,
            'keyword' => $keyword,
            'typeFinancement' => $typeFinancement,
            'tauxMin' => $tauxMin,
            'tauxMax' => $tauxMax,
            'montant' => $montant,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'dashboardStats' => $dashboardStats,
            'typesDisponibles' => $typesDisponibles,
            'offresCounts' => $offresCounts,
        ]);
    }

    #[Route('/new', name: 'admin_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $produit = new ProduitFinancier();
        $form = $this->createForm(ProduitFinancierType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($produit);
            $em->flush();
            $this->addFlash('success', 'Produit financier créé avec succès !');
            return $this->redirectToRoute('admin_produit_index');
        }

        return $this->render('admin/produit/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/stats', name: 'admin_produit_stats', methods: ['GET'])]
    public function stats(): Response
    {
        $dashboardStats = $this->productService->getDashboardStatistics();

        return $this->render('admin/produit/stats.html.twig', [
            'dashboardStats' => $dashboardStats,
        ]);
    }

    #[Route('/simulateur', name: 'admin_produit_simulateur', methods: ['GET', 'POST'])]
    public function simulateur(Request $request, ProduitFinancierRepository $repository): Response
    {
        $produits = $repository->findAll();
        $simulation = null;
        $produitSelectionne = null;

        if ($request->isMethod('POST')) {
            $montant = (float)$request->request->get('montant', 0);
            $dureeMois = (int)$request->request->get('duree', 12);
            $produitId = (int)$request->request->get('produit_id', 0);
            $typeClient = $request->request->get('type_client', 'agriculture');

            if ($produitId > 0) {
                $produitSelectionne = $repository->find($produitId);
                if ($produitSelectionne) {
                    $simulation = $this->productService->simulerCredit(
                        $montant,
                        $produitSelectionne->getTauxInteret() ?? 0.0,
                        $dureeMois
                    );
                }
            } else {
                $taux = (float)$request->request->get('taux', 0);
                $simulation = $this->productService->simulerCredit($montant, $taux, $dureeMois);
            }
        }

        return $this->render('admin/produit/simulateur.html.twig', [
            'produits' => $produits,
            'simulation' => $simulation,
            'produitSelectionne' => $produitSelectionne,
            'typeClient' => $request->request->get('type_client', 'agriculture'),
        ]);
    }

    #[Route('/simulateur-ajax', name: 'admin_produit_simulateur_ajax', methods: ['POST'])]
    public function simulateurAjax(Request $request, ProduitFinancierRepository $repository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $montant = (float)($data['montant'] ?? 0);
        $dureeMois = (int)($data['duree'] ?? 12);
        $produitId = (int)($data['produit_id'] ?? 0);
        $taux = (float)($data['taux'] ?? 0);

        if ($produitId > 0) {
            $produit = $repository->find($produitId);
            if ($produit) {
                $taux = $produit->getTauxInteret() ?? $taux;
            }
        }

        $simulation = $this->productService->simulerCredit($montant, $taux, $dureeMois);

        // Limiter le tableau d'amortissement à 12 premières lignes pour l'AJAX
        if (isset($simulation['tableau'])) {
            $simulation['tableauPreview'] = array_slice($simulation['tableau'], 0, 12);
            unset($simulation['tableau']);
        }

        return new JsonResponse($simulation);
    }

    #[Route('/eligibilite/{id}', name: 'admin_produit_eligibilite', methods: ['POST'])]
    public function eligibilite(ProduitFinancier $produit, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $montant = (float)($data['montant'] ?? 0);

        $result = $this->productService->verifierEligibilite($produit, $montant);

        return new JsonResponse($result);
    }

    #[Route('/comparer', name: 'admin_produit_comparer', methods: ['POST'])]
    public function comparer(Request $request, ProduitFinancierRepository $repository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $id1 = (int)($data['produit1'] ?? 0);
        $id2 = (int)($data['produit2'] ?? 0);

        $p1 = $repository->find($id1);
        $p2 = $repository->find($id2);

        if (!$p1 || !$p2) {
            return new JsonResponse(['error' => 'Produit(s) introuvable(s)'], 404);
        }

        $comparison = $this->productService->comparerProduits($p1, $p2);
        return new JsonResponse($comparison);
    }

    #[Route('/export-csv', name: 'admin_produit_export_csv', methods: ['GET'])]
    public function exportCsv(): StreamedResponse
    {
        $data = $this->productService->getExportData();

        $response = new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 pour Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // En-têtes
            if (!empty($data)) {
                fputcsv($handle, array_keys($data[0]), ';');
            }

            // Données
            foreach ($data as $row) {
                fputcsv($handle, $row, ';');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="produits_financiers_' . date('Y-m-d') . '.csv"');

        return $response;
    }

    #[Route('/export-pdf', name: 'admin_produit_export_pdf', methods: ['GET'])]
    public function exportPdf(ProduitFinancierRepository $repository): Response
    {
        $produits = $repository->findAll();
        $stats = $repository->getStatistics();
        $byType = $repository->countByType();

        return $this->pdfService->generatePdfResponse(
            'pdf/produits.html.twig',
            [
                'produits' => $produits,
                'stats' => $stats,
                'byType' => $byType,
                'date' => new \DateTime(),
            ],
            'produits_financiers_' . date('Y-m-d') . '.pdf',
            'A4',
            'landscape'
        );
    }

    #[Route('/simulateur-pdf', name: 'admin_produit_simulateur_pdf', methods: ['POST'])]
    public function simulateurPdf(Request $request, ProduitFinancierRepository $repository): Response
    {
        $montant = (float)$request->request->get('montant', 0);
        $dureeMois = (int)$request->request->get('duree', 12);
        $produitId = (int)$request->request->get('produit_id', 0);
        $taux = (float)$request->request->get('taux', 0);
        $typeClient = $request->request->get('type_client', 'agriculture');

        $produit = null;
        if ($produitId > 0) {
            $produit = $repository->find($produitId);
            if ($produit) {
                $taux = $produit->getTauxInteret() ?? $taux;
            }
        }

        $simulation = $this->productService->simulerCredit($montant, $taux, $dureeMois);

        if (isset($simulation['error'])) {
            $this->addFlash('error', $simulation['error']);
            return $this->redirectToRoute('admin_produit_simulateur');
        }

        return $this->pdfService->generatePdfResponse(
            'pdf/simulateur.html.twig',
            [
                'simulation' => $simulation,
                'produit' => $produit,
                'typeClient' => $typeClient,
                'date' => new \DateTime(),
            ],
            'simulation_credit_' . date('Y-m-d') . '.pdf'
        );
    }

    #[Route('/{id}', name: 'admin_produit_show', methods: ['GET'])]
    public function show(ProduitFinancier $produit): Response
    {
        return $this->render('admin/produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ProduitFinancier $produit, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProduitFinancierType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Produit financier mis à jour !');
            return $this->redirectToRoute('admin_produit_index');
        }

        return $this->render('admin/produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_produit_delete', methods: ['POST'])]
    public function delete(Request $request, ProduitFinancier $produit, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $produit->getId(), $request->request->get('_token'))) {
            $em->remove($produit);
            $em->flush();
            $this->addFlash('success', 'Produit supprimé avec succès !');
        }

        return $this->redirectToRoute('admin_produit_index');
    }
}
