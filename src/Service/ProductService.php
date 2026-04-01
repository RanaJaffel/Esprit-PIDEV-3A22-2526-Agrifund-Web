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
    public function createProduct(string $nomProduit, string $typeFinancement, float $tauxInteret, float $montantMin, float $montantMax, string $reglesFinancieres): ProduitFinancier
    {
        $product = new ProduitFinancier();
        $product->setNomProduit($nomProduit);
        $product->setTypeFinancement($typeFinancement);
        $product->setTauxInteret($tauxInteret);
        $product->setMontantMin($montantMin);
        $product->setMontantMax($montantMax);
        $product->setReglesFinancieres($reglesFinancieres);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }

    /**
     * Update product
     */
    public function updateProduct(ProduitFinancier $product, string $nomProduit, string $typeFinancement, float $tauxInteret, float $montantMin, float $montantMax, string $reglesFinancieres): ProduitFinancier
    {
        $product->setNomProduit($nomProduit);
        $product->setTypeFinancement($typeFinancement);
        $product->setTauxInteret($tauxInteret);
        $product->setMontantMin($montantMin);
        $product->setMontantMax($montantMax);
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
        return count($this->repository->findAll());
    }
}
