<?php

namespace App\Service;

use App\Entity\ProduitFinancier;
use App\Repository\ProduitFinancierRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductService
{
    public function __construct(
        private ProduitFinancierRepository $repository,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Get all active products (Financial Products)
     */
    public function getAllActiveProducts(): array
    {
        return $this->repository->findAll();
    }

    /**
     * Get product by ID
     */
    public function getProductById(int $id): ?ProduitFinancier
    {
        return $this->repository->find($id);
    }

    /**
     * Create a new product
     */
    public function createProduct(string $nomProduit, string $typeFinancement, float $tauxInteret, float $montant, string $reglesFinancieres): ProduitFinancier
    {
        $product = new ProduitFinancier();
        $product->setNomProduit($nomProduit);
        $product->setTypeFinancement($typeFinancement);
        $product->setTauxInteret($tauxInteret);
        $product->setMontant($montant);
        $product->setReglesFinancieres($reglesFinancieres);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }

    /**
     * Update product
     */
    public function updateProduct(ProduitFinancier $product, string $nomProduit, string $typeFinancement, float $tauxInteret, float $montant, string $reglesFinancieres): ProduitFinancier
    {
        $product->setNomProduit($nomProduit);
        $product->setTypeFinancement($typeFinancement);
        $product->setTauxInteret($tauxInteret);
        $product->setMontant($montant);
        $product->setReglesFinancieres($reglesFinancieres);

        $this->entityManager->flush();

        return $product;
    }

    /**
     * Delete product
     */
    public function deleteProduct(ProduitFinancier $product): void
    {
        $this->entityManager->remove($product);
        $this->entityManager->flush();
    }

    /**
     * Get all products
     */
    public function getAllProducts(): array
    {
        return $this->repository->findAll();
    }

    /**
     * Get products by type
     */
    public function getProductsByType(string $type): array
    {
        return $this->repository->findByType($type);
    }

    /**
     * Get active products count
     */
    public function getActiveProductsCount(): int
    {
        return $this->repository->countAllProducts();
    }

    // =====================================================
    // MÉTIER AVANCÉ — Business Logic
    // =====================================================

    /**
     * Simulateur de crédit — calcul des mensualités
     * Formule d'amortissement constant (annuité constante)
     */
    public function simulerCredit(float $montant, float $tauxAnnuel, int $dureeMois): array
    {
        if ($montant <= 0 || $tauxAnnuel < 0 || $dureeMois <= 0) {
            return ['error' => 'Paramètres invalides'];
        }

        $tauxMensuel = ($tauxAnnuel / 100) / 12;

        if ($tauxMensuel == 0) {
            $mensualite = $montant / $dureeMois;
            $coutTotal = $montant;
            $totalInterets = 0;
        } else {
            // Formule d'annuité constante : M = P * [r(1+r)^n] / [(1+r)^n - 1]
            $mensualite = $montant * ($tauxMensuel * pow(1 + $tauxMensuel, $dureeMois))
                         / (pow(1 + $tauxMensuel, $dureeMois) - 1);
            $coutTotal = $mensualite * $dureeMois;
            $totalInterets = $coutTotal - $montant;
        }

        // Tableau d'amortissement
        $tableauAmortissement = [];
        $capitalRestant = $montant;

        for ($i = 1; $i <= min($dureeMois, 360); $i++) {
            $interetMois = $capitalRestant * $tauxMensuel;
            $capitalMois = $mensualite - $interetMois;
            $capitalRestant -= $capitalMois;

            if ($capitalRestant < 0) {
                $capitalRestant = 0;
            }

            $tableauAmortissement[] = [
                'mois' => $i,
                'mensualite' => round($mensualite, 2),
                'capital' => round($capitalMois, 2),
                'interet' => round($interetMois, 2),
                'capitalRestant' => round($capitalRestant, 2),
            ];
        }

        return [
            'montant' => $montant,
            'tauxAnnuel' => $tauxAnnuel,
            'dureeMois' => $dureeMois,
            'mensualite' => round($mensualite, 2),
            'coutTotal' => round($coutTotal, 2),
            'totalInterets' => round($totalInterets, 2),
            'ratioInterets' => $montant > 0 ? round(($totalInterets / $montant) * 100, 1) : 0,
            'tableau' => $tableauAmortissement,
        ];
    }

    /**
     * Comparer deux produits financiers
     */
    public function comparerProduits(ProduitFinancier $produit1, ProduitFinancier $produit2): array
    {
        $diffTaux = $produit1->getTauxInteret() - $produit2->getTauxInteret();
        $productIds = [];
        foreach ([$produit1, $produit2] as $produit) {
            if ($produit->getId() !== null) {
                $productIds[] = $produit->getId();
            }
        }
        $offresCounts = $this->repository->countOffersByProductIds($productIds);

        return [
            'produit1' => [
                'id' => $produit1->getId(),
                'nom' => $produit1->getNomProduit(),
                'type' => $produit1->getTypeFinancement(),
                'taux' => $produit1->getTauxInteret(),
                'montant' => $produit1->getMontant(),
                'nbOffres' => $offresCounts[$produit1->getId()] ?? 0,
            ],
            'produit2' => [
                'id' => $produit2->getId(),
                'nom' => $produit2->getNomProduit(),
                'type' => $produit2->getTypeFinancement(),
                'taux' => $produit2->getTauxInteret(),
                'montant' => $produit2->getMontant(),
                'nbOffres' => $offresCounts[$produit2->getId()] ?? 0,
            ],
            'differencesTaux' => round($diffTaux, 2),
            'meilleureOption' => $diffTaux <= 0 ? $produit1->getNomProduit() : $produit2->getNomProduit(),
        ];
    }

    /**
     * Obtenir les statistiques complètes pour le dashboard
     */
    public function getDashboardStatistics(): array
    {
        $stats = $this->repository->getStatistics();
        $byType = $this->repository->countByType();
        $tauxDist = $this->repository->getTauxDistribution();
        $popular = $this->repository->findMostPopular(5);
        $popularIds = [];
        foreach ($popular as $produit) {
            if ($produit instanceof ProduitFinancier && $produit->getId() !== null) {
                $popularIds[] = $produit->getId();
            }
        }
        $popularCounts = $this->repository->countOffersByProductIds($popularIds);

        return [
            'stats' => $stats,
            'byType' => $byType,
            'tauxDistribution' => $tauxDist,
            'topProduits' => $popular,
            'topProduitsOffresCount' => $popularCounts,
        ];
    }

    /**
     * Vérifier l'éligibilité d'un montant pour un produit
     */
    public function verifierEligibilite(ProduitFinancier $produit, float $montantDemande): array
    {
        $eligible = $montantDemande > 0
                 && $montantDemande <= (float) $produit->getMontant();

        $raisons = [];
        if ($montantDemande <= 0) {
            $raisons[] = 'Le montant demande doit etre superieur a 0 DT.';
        }
        if ($montantDemande > (float) $produit->getMontant()) {
            $raisons[] = sprintf(
                'Le montant demande (%.0f DT) depasse le montant autorise pour ce produit (%.0f DT).',
                $montantDemande,
                $produit->getMontant()
            );
        }

        // Suggestion de simulation si éligible
        $simulation = null;
        if ($eligible) {
            $simulation = $this->simulerCredit($montantDemande, $produit->getTauxInteret() ?? 0.0, 60);
            unset($simulation['tableau']); // Pas besoin du tableau complet ici
        }

        return [
            'eligible' => $eligible,
            'produit' => $produit->getNomProduit(),
            'montantDemande' => $montantDemande,
            'raisons' => $raisons,
            'simulation' => $simulation,
        ];
    }

    /**
     * Exporter les données des produits pour un tableau récapitulatif
     */
    public function getExportData(): array
    {
        $produits = $this->repository->findAll();
        $productIds = [];
        foreach ($produits as $produit) {
            if ($produit->getId() !== null) {
                $productIds[] = $produit->getId();
            }
        }
        $offresCounts = $this->repository->countOffersByProductIds($productIds);
        $data = [];

        foreach ($produits as $produit) {
            $data[] = [
                'ID' => $produit->getId(),
                'Nom' => $produit->getNomProduit(),
                'Type' => $produit->getTypeFinancement(),
                'Taux' => $produit->getTauxInteret() . '%',
                'Montant' => number_format((float) $produit->getMontant(), 0, ',', ' ') . ' DT',
                'Nb Offres' => $offresCounts[$produit->getId()] ?? 0,
                'Règles' => $produit->getReglesFinancieres() ?? 'N/A',
            ];
        }

        return $data;
    }
}
