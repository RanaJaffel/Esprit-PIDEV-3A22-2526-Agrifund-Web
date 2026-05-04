<?php

namespace App\Repository;

use App\Entity\ProduitFinancier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProduitFinancier>
 */
class ProduitFinancierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProduitFinancier::class);
    }

    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.typeFinancement = :type')
            ->setParameter('type', $type)
            ->orderBy('p.nomProduit', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.nomProduit', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return ProduitFinancier[]
     */
    public function findLimited(int $limit = 8): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.nomProduit', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findOneByName(string $name): ?ProduitFinancier
    {
        return $this->createQueryBuilder('p')
            ->andWhere('LOWER(p.nomProduit) = :name')
            ->setParameter('name', mb_strtolower(trim($name)))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countAllProducts(): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param int[] $productIds
     *
     * @return array<int, int>
     */
    public function countOffersByProductIds(array $productIds): array
    {
        $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds))));
        if ($productIds === []) {
            return [];
        }

        $rows = $this->createQueryBuilder('p')
            ->select('p.id AS productId, COUNT(o.id) AS offerCount')
            ->leftJoin('p.offres', 'o')
            ->andWhere('p.id IN (:productIds)')
            ->setParameter('productIds', $productIds)
            ->groupBy('p.id')
            ->getQuery()
            ->getArrayResult();

        $counts = array_fill_keys($productIds, 0);
        foreach ($rows as $row) {
            $counts[(int) $row['productId']] = (int) $row['offerCount'];
        }

        return $counts;
    }

    /**
     * Recherche avancée avec filtres multiples
     */
    public function searchAndFilter(
        ?string $keyword = null,
        ?string $typeFinancement = null,
        ?float $tauxMin = null,
        ?float $tauxMax = null,
        ?float $montantMin = null,
        ?float $montantMax = null,
        string $sortBy = 'nomProduit',
        string $sortOrder = 'ASC'
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('p');

        if ($keyword) {
            $qb->andWhere('p.nomProduit LIKE :keyword OR p.reglesFinancieres LIKE :keyword')
               ->setParameter('keyword', '%' . $keyword . '%');
        }

        if ($typeFinancement) {
            $qb->andWhere('p.typeFinancement = :type')
               ->setParameter('type', $typeFinancement);
        }

        if ($tauxMin !== null) {
            $qb->andWhere('p.tauxInteret >= :tauxMin')
               ->setParameter('tauxMin', $tauxMin);
        }

        if ($tauxMax !== null) {
            $qb->andWhere('p.tauxInteret <= :tauxMax')
               ->setParameter('tauxMax', $tauxMax);
        }

        if ($montantMin !== null) {
            $qb->andWhere('p.montant >= :montantFilterMin')
               ->setParameter('montantFilterMin', $montantMin);
        }

        if ($montantMax !== null) {
            $qb->andWhere('p.montant <= :montantFilterMax')
               ->setParameter('montantFilterMax', $montantMax);
        }

        // Tri dynamique
        $allowedSorts = [
            'nomProduit' => 'nomProduit',
            'typeFinancement' => 'typeFinancement',
            'tauxInteret' => 'tauxInteret',
            'montant' => 'montant',
            // Backward compatibility for existing query strings.
            'montantMin' => 'montant',
            'montantMax' => 'montant',
        ];
        $sortBy = $allowedSorts[$sortBy] ?? 'nomProduit';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        $qb->orderBy('p.' . $sortBy, $sortOrder);

        return $qb;
    }

    /**
     * Statistiques globales des produits financiers (Optimisé: 1 seule requête)
     */
    public function getStatistics(): array
    {
        $stats = $this->createQueryBuilder('p')
            ->select('COUNT(p.id) as total')
            ->addSelect('AVG(p.tauxInteret) as avgTaux')
            ->addSelect('MIN(p.tauxInteret) as minTaux')
            ->addSelect('MAX(p.tauxInteret) as maxTaux')
            ->addSelect('AVG(p.montant) as avgMontant')
            ->addSelect('MAX(p.montant) as maxMontant')
            ->getQuery()
            ->getSingleResult();

        $avgMontant = round((float)($stats['avgMontant'] ?? 0), 0);

        return [
            'totalProduits' => (int)$stats['total'],
            'avgTaux' => round((float)$stats['avgTaux'], 2),
            'minTaux' => round((float)($stats['minTaux'] ?? 0), 2),
            'maxTaux' => round((float)($stats['maxTaux'] ?? 0), 2),
            'avgMontant' => $avgMontant,
            'avgMontantMin' => $avgMontant,
            'avgMontantMax' => $avgMontant,
            'maxMontant' => round((float)($stats['maxMontant'] ?? 0), 0),
        ];
    }

    /**
     * Nombre de produits par type de financement
     */
    public function countByType(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.typeFinancement, COUNT(p.id) as total')
            ->groupBy('p.typeFinancement')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Produits les plus populaires (avec le plus d'offres)
     */
    public function findMostPopular(int $limit = 5): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.offres', 'o')
            ->addSelect('COUNT(o.id) as HIDDEN offreCount')
            ->groupBy('p.id')
            ->orderBy('offreCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Distribution des taux d'intérêt par tranche
     *
     * @return array<string, int>
     */
    public function getTauxDistribution(): array
    {
        $row = $this->createQueryBuilder('p')
            ->select('SUM(CASE WHEN p.tauxInteret < 3 THEN 1 ELSE 0 END) AS range0')
            ->addSelect('SUM(CASE WHEN p.tauxInteret >= 3 AND p.tauxInteret < 5 THEN 1 ELSE 0 END) AS range1')
            ->addSelect('SUM(CASE WHEN p.tauxInteret >= 5 AND p.tauxInteret < 8 THEN 1 ELSE 0 END) AS range2')
            ->addSelect('SUM(CASE WHEN p.tauxInteret >= 8 AND p.tauxInteret < 12 THEN 1 ELSE 0 END) AS range3')
            ->addSelect('SUM(CASE WHEN p.tauxInteret >= 12 THEN 1 ELSE 0 END) AS range4')
            ->getQuery()
            ->getSingleResult();

        return [
            '0-3%' => (int) ($row['range0'] ?? 0),
            '3-5%' => (int) ($row['range1'] ?? 0),
            '5-8%' => (int) ($row['range2'] ?? 0),
            '8-12%' => (int) ($row['range3'] ?? 0),
            '12%+' => (int) ($row['range4'] ?? 0),
        ];
    }

    /**
     * Retourne les types de financement distincts existants
     */
    public function findDistinctTypes(): array
    {
        $results = $this->createQueryBuilder('p')
            ->select('DISTINCT p.typeFinancement')
            ->orderBy('p.typeFinancement', 'ASC')
            ->getQuery()
            ->getResult();

        return array_column($results, 'typeFinancement');
    }
}
