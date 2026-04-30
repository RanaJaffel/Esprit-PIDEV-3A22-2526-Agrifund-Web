<?php

namespace App\Controller;

use App\Repository\OffreFinanciereRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/offers', name: 'app_offer_')]
class OfferController extends AbstractController
{
    public function __construct(private OffreFinanciereRepository $offerRepository)
    {
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        $offers = $this->offerRepository->findActiveOffers();

        return $this->render('offers.html.twig', [
            'offers' => $offers,
        ]);
    }

    #[Route('/{id}', name: 'detail', requirements: ['id' => '\d+'])]
    public function detail(int $id): Response
    {
        $offer = $this->offerRepository->find($id);

        if (!$offer) {
            throw $this->createNotFoundException('L\'offre n\'a pas été trouvée');
        }

        return $this->render('offer-detail.html.twig', [
            'offer' => $offer,
        ]);
    }
}
