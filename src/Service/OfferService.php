<?php

namespace App\Service;

use App\Entity\OffreFinanciere;
use App\Entity\ProduitFinancier;
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
        return $this->repository->countActiveOffers();
    }

    // =====================================================
    // MÉTIER AVANCÉ — Business Logic
    // =====================================================

    /**
     * Obtenir les statistiques complètes du dashboard offres
     */
    public function getDashboardStatistics(): array
    {
        $stats = $this->repository->getStatistics();
        $byStatut = $this->repository->countByStatut();
        $byProduit = $this->repository->countByProduit();
        $recent = $this->repository->findRecent(5);

        return [
            'stats' => $stats,
            'byStatut' => $byStatut,
            'byProduit' => $byProduit,
            'recentOffres' => $recent,
        ];
    }

    /**
     * Changer le statut d'une offre avec validation métier
     */
    public function changerStatut(OffreFinanciere $offre, string $nouveauStatut): array
    {
        $ancienStatut = $offre->getStatut();
        $statutsValides = ['Active', 'En attente', 'Cancelled'];

        if (!in_array($nouveauStatut, $statutsValides)) {
            return [
                'success' => false,
                'message' => 'Statut invalide. Statuts autorisés : ' . implode(', ', $statutsValides),
            ];
        }

        // Règle métier : une offre Cancelled ne peut pas redevenir Active directement
        if ($ancienStatut === 'Cancelled' && $nouveauStatut === 'Active') {
            return [
                'success' => false,
                'message' => 'Une offre annulée ne peut pas être réactivée directement. Elle doit d\'abord passer par le statut "En attente".',
            ];
        }

        $offre->setStatut($nouveauStatut);
        $this->entityManager->flush();

        return [
            'success' => true,
            'message' => sprintf('Statut changé de "%s" à "%s" avec succès.', $ancienStatut, $nouveauStatut),
            'ancienStatut' => $ancienStatut,
            'nouveauStatut' => $nouveauStatut,
        ];
    }

    /**
     * Dupliquer une offre existante
     */
    public function dupliquerOffre(OffreFinanciere $offreOriginale): OffreFinanciere
    {
        $nouvOffre = new OffreFinanciere();
        $nouvOffre->setNomOffre($offreOriginale->getNomOffre() . ' (copie)');
        $nouvOffre->setConditions($offreOriginale->getConditions());
        $nouvOffre->setStatut('En attente');
        $nouvOffre->setProduitFinancier($offreOriginale->getProduitFinancier());

        $this->entityManager->persist($nouvOffre);
        $this->entityManager->flush();

        return $nouvOffre;
    }

    /**
     * Obtenir le résumé d'une offre avec son produit
     */
    public function getOffreResume(OffreFinanciere $offre): array
    {
        $produit = $offre->getProduitFinancier();

        return [
            'offre' => [
                'id' => $offre->getId(),
                'nom' => $offre->getNomOffre(),
                'conditions' => $offre->getConditions(),
                'statut' => $offre->getStatut(),
            ],
            'produit' => $produit ? [
                'id' => $produit->getId(),
                'nom' => $produit->getNomProduit(),
                'type' => $produit->getTypeFinancement(),
                'taux' => $produit->getTauxInteret(),
                'montant' => $produit->getMontant(),
            ] : null,
        ];
    }

    /**
     * Activer en masse toutes les offres en attente d'un produit
     */
    public function activerOffresParProduit(ProduitFinancier $produit): int
    {
        $produitId = $produit->getId();
        if ($produitId === null) {
            return 0;
        }

        $offres = $this->repository->findByProduit($produitId);
        $count = 0;

        foreach ($offres as $offre) {
            if ($offre->getStatut() === 'En attente') {
                $offre->setStatut('Active');
                $count++;
            }
        }

        if ($count > 0) {
            $this->entityManager->flush();
        }

        return $count;
    }

    /**
     * Annuler en masse toutes les offres d'un produit
     */
    public function annulerOffresParProduit(ProduitFinancier $produit): int
    {
        $produitId = $produit->getId();
        if ($produitId === null) {
            return 0;
        }

        $offres = $this->repository->findByProduit($produitId);
        $count = 0;

        foreach ($offres as $offre) {
            if ($offre->getStatut() !== 'Cancelled') {
                $offre->setStatut('Cancelled');
                $count++;
            }
        }

        if ($count > 0) {
            $this->entityManager->flush();
        }

        return $count;
    }

    /**
     * Export CSV-ready data
     */
    public function getExportData(): array
    {
        $offres = $this->repository->findAll();
        $data = [];

        foreach ($offres as $offre) {
            $produit = $offre->getProduitFinancier();
            $data[] = [
                'ID' => $offre->getId(),
                'Nom' => $offre->getNomOffre(),
                'Statut' => $offre->getStatut(),
                'Conditions' => $offre->getConditions() ?? 'N/A',
                'Produit' => $produit ? $produit->getNomProduit() : 'N/A',
                'Type Financement' => $produit ? $produit->getTypeFinancement() : 'N/A',
                'Taux' => $produit ? $produit->getTauxInteret() . '%' : 'N/A',
            ];
        }

        return $data;
    }
}
