<?php

namespace App\Service;

use App\Entity\OffreFinanciere;
use App\Repository\OffreFinanciereRepository;
use Doctrine\ORM\EntityManagerInterface;

class OfferService
{
    public function __construct(
        private OffreFinanciereRepository $repository,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Get all active offers (Financial Offers)
     */
    public function getAllActiveOffers(): array
    {
        return $this->repository->findActiveOffers();
    }

    /**
     * Get offer by ID
     */
    public function getOfferById(int $id): ?OffreFinanciere
    {
        return $this->repository->find($id);
    }

    /**
     * Create a new offer
     */
    public function createOffer(string $nomOffre, string $conditions, string $statut, $produitFinancier): OffreFinanciere
    {
        $offer = new OffreFinanciere();
        $offer->setNomOffre($nomOffre);
        $offer->setConditions($conditions);
        $offer->setStatut($statut);
        $offer->setProduitFinancier($produitFinancier);

        $this->entityManager->persist($offer);
        $this->entityManager->flush();

        return $offer;
    }

    /**
     * Update offer
     */
    public function updateOffer(OffreFinanciere $offer, string $nomOffre, string $conditions, string $statut): OffreFinanciere
    {
        $offer->setNomOffre($nomOffre);
        $offer->setConditions($conditions);
        $offer->setStatut($statut);

        $this->entityManager->flush();

        return $offer;
    }

    /**
     * Delete offer
     */
    public function deleteOffer(OffreFinanciere $offer): void
    {
        $this->entityManager->remove($offer);
        $this->entityManager->flush();
    }

    /**
     * Get all offers
     */
    public function getAllOffers(): array
    {
        return $this->repository->findAll();
    }

    /**
     * Get active offers count
     */
    public function getActiveOffersCount(): int
    {
        return count($this->repository->findActiveOffers());
    }
}
