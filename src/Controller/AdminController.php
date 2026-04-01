<?php

namespace App\Controller;

use App\Repository\ProduitFinancierRepository;
use App\Repository\OffreFinanciereRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard', methods: ['GET'])]
    public function dashboard(
        ProduitFinancierRepository $produitRepository,
        OffreFinanciereRepository $offreRepository
    ): Response
    {
        $totalProducts = count($produitRepository->findAll());
        $totalOffers = count($offreRepository->findAll());
        $activeOffers = count($offreRepository->findActiveOffers());

        return $this->render('admin/dashboard.html.twig', [
            'totalProducts' => $totalProducts,
            'totalOffers' => $totalOffers,
            'activeOffers' => $activeOffers,
        ]);
    }
}
