<?php

namespace App\Controller;

use App\Service\GalleryService;
use App\Service\ProductService;
use App\Service\OfferService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'app_')]
class ArialogicController extends AbstractController
{
    public function __construct(
        private GalleryService $galleryService,
        private ProductService $productService,
        private OfferService $offerService
    ) {}

    /**
     * Homepage displaying featured content
     */
    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('index.html.twig', [
            'title' => 'Home',
        ]);
    }

    /**
     * Display gallery with items
     */
    #[Route(path: 'gallery', name: 'gallery', methods: ['GET'])]
    public function gallery(): Response
    {
        $galleryItems = $this->galleryService->getActiveGallery();
        $itemCount = $this->galleryService->getActiveItemsCount();

        return $this->render('gallery.html.twig', [
            'galleryItems' => $galleryItems,
            'itemCount' => $itemCount,
            'title' => 'Gallery',
        ]);
    }

    /**
     * Full-width page layout
     */
    #[Route(path: 'full-width', name: 'full_width', methods: ['GET'])]
    public function fullWidth(): Response
    {
        return $this->render('full-width.html.twig', [
            'title' => 'Full Width',
        ]);
    }

    /**
     * Grid layout demonstration
     */
    #[Route(path: 'basic-grid', name: 'basic_grid', methods: ['GET'])]
    public function basicGrid(): Response
    {
        return $this->render('basic-grid.html.twig', [
            'title' => 'Basic Grid',
        ]);
    }

    /**
     * Page with left sidebar
     */
    #[Route(path: 'sidebar-left', name: 'sidebar_left', methods: ['GET'])]
    public function sidebarLeft(): Response
    {
        return $this->render('sidebar-left.html.twig', [
            'title' => 'Sidebar Left',
        ]);
    }

    /**
     * Page with right sidebar
     */
    #[Route(path: 'sidebar-right', name: 'sidebar_right', methods: ['GET'])]
    public function sidebarRight(): Response
    {
        return $this->render('sidebar-right.html.twig', [
            'title' => 'Sidebar Right',
        ]);
    }

    /**
     * Display a single page by slug (deprecated - Page entity removed)
     */
    #[Route(path: 'page/{slug}', name: 'page_show', methods: ['GET'])]
    public function showPage(string $slug): Response
    {
        throw $this->createNotFoundException('Page not found - this feature has been replaced with Financial Products');
    }

    /**
     * Products listing page
     */
    #[Route(path: 'produits', name: 'products', methods: ['GET'])]
    public function products(): Response
    {
        $products = $this->productService->getAllActiveProducts();

        return $this->render('products.html.twig', [
            'products' => $products,
            'title' => 'Products',
        ]);
    }

    /**
     * Offers and special deals page
     */
    #[Route(path: 'offres', name: 'offers', methods: ['GET'])]
    public function offers(): Response
    {
        $offers = $this->offerService->getAllActiveOffers();

        return $this->render('offers.html.twig', [
            'offers' => $offers,
            'title' => 'Special Offers',
        ]);
    }

    /**
     * Product detail page
     */
    #[Route(path: 'produit/{id}', name: 'product_detail', methods: ['GET'])]
    public function showProduct(int $id): Response
    {
        $product = $this->productService->getProductById($id);

        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        return $this->render('product-detail.html.twig', [
            'product' => $product,
            'title' => $product->getNomProduit(),
        ]);
    }

    /**
     * Offer detail page
     */
    #[Route(path: 'offre/{id}', name: 'offer_detail', methods: ['GET'])]
    public function showOffer(int $id): Response
    {
        $offer = $this->offerService->getOfferById($id);

        if (!$offer) {
            throw $this->createNotFoundException('Offer not found');
        }

        return $this->render('offer-detail.html.twig', [
            'offer' => $offer,
            'title' => $offer->getNomOffre(),
        ]);
    }
}
