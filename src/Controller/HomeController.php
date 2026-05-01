<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Service\OfferService;
use App\Service\PdfService;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private ProductService $productService,
        private OfferService $offerService,
        private PdfService $pdfService
    ) {}

    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'title' => 'Bienvenue chez AgriFund',
        ]);
    }

    #[Route('/about', name: 'app_about', methods: ['GET'])]
    public function about(): Response
    {
        return $this->render('home/about.html.twig', [
            'title' => 'À propos de nous',
        ]);
    }

    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function contact(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $this->addFlash('success', 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.');
            return $this->redirectToRoute('app_contact');
        }

        return $this->render('home/contact.html.twig', [
            'title' => 'Contactez-nous',
        ]);
    }

    /**
     * Liste des produits financiers disponibles
     */
    #[Route('/produits', name: 'app_products', methods: ['GET'])]
    public function products(): Response
    {
        $products = $this->productService->getAllActiveProducts();

        return $this->render('products.html.twig', [
            'products' => $products,
            'title' => 'Nos Produits Financiers',
        ]);
    }

    /**
     * Liste des offres financières spéciales
     */
    #[Route('/offres', name: 'app_offers', methods: ['GET'])]
    public function offers(): Response
    {
        $offers = $this->offerService->getAllActiveOffers();

        return $this->render('offers.html.twig', [
            'offers' => $offers,
            'title' => 'Nos Offres Spéciales',
        ]);
    }

    /**
     * Détail d'un produit financier
     */
    #[Route('/produit/{id}', name: 'app_product_detail', methods: ['GET'])]
    public function showProduct(int $id): Response
    {
        $product = $this->productService->getProductById($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        return $this->render('product-detail.html.twig', [
            'product' => $product,
            'title' => $product->getNomProduit(),
        ]);
    }

    #[Route('/produit/{id}/pdf', name: 'app_product_detail_pdf', methods: ['GET'])]
    public function showProductPdf(int $id): Response
    {
        $product = $this->productService->getProductById($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouve');
        }

        $clientName = 'Client AgriFund';
        $user = $this->getUser();
        if ($user instanceof Utilisateur) {
            $clientName = trim($user->getNomComplet()) !== ''
                ? $user->getNomComplet()
                : $user->getUserIdentifier();
        }

        return $this->pdfService->generatePdfResponse(
            'pdf/produit_detail_personnalise.html.twig',
            [
                'product' => $product,
                'date' => new \DateTimeImmutable(),
                'client_name' => $clientName,
            ],
            sprintf('fiche_produit_%d_%s.pdf', $product->getId(), date('Y-m-d')),
            'A4',
            'portrait'
        );
    }

    /**
     * Détail d'une offre financière
     */
    #[Route('/offre/{id}', name: 'app_offer_detail', methods: ['GET'])]
    public function showOffer(int $id): Response
    {
        $offer = $this->offerService->getOfferById($id);

        if (!$offer) {
            throw $this->createNotFoundException('Offre non trouvée');
        }

        return $this->render('offer-detail.html.twig', [
            'offer' => $offer,
            'title' => $offer->getNomOffre(),
        ]);
    }
}