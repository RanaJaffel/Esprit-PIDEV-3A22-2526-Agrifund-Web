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
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.offres', 'o')
            ->addSelect('o');

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
            $qb->andWhere('p.montantMin >= :montantFilterMin')
               ->setParameter('montantFilterMin', $montantMin);
        }

        if ($montantMax !== null) {
            $qb->andWhere('p.montantMax <= :montantFilterMax')
               ->setParameter('montantFilterMax', $montantMax);
        }

        // Tri dynamique
        $allowedSorts = ['nomProduit', 'typeFinancement', 'tauxInteret', 'montantMin', 'montantMax'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'nomProduit';
        }
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
            ->addSelect('AVG(p.montantMin) as avgMontantMin')
            ->addSelect('AVG(p.montantMax) as avgMontantMax')
            ->addSelect('MAX(p.montantMax) as maxMontant')
            ->getQuery()
            ->getSingleResult();

        return [
            'totalProduits' => (int)$stats['total'],
            'avgTaux' => round((float)$stats['avgTaux'], 2),
            'minTaux' => round((float)($stats['minTaux'] ?? 0), 2),
            'maxTaux' => round((float)($stats['maxTaux'] ?? 0), 2),
            'avgMontantMin' => round((float)($stats['avgMontantMin'] ?? 0), 0),
            'avgMontantMax' => round((float)($stats['avgMontantMax'] ?? 0), 0),
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
     */
    public function getTauxDistribution(): array
    {
        $results = $this->createQueryBuilder('p')
            ->select('p.tauxInteret')
            ->orderBy('p.tauxInteret', 'ASC')
            ->getQuery()
            ->getResult();

        $distribution = [
            '0-3%' => 0,
            '3-5%' => 0,
            '5-8%' => 0,
            '8-12%' => 0,
            '12%+' => 0,
        ];

        foreach ($results as $row) {
            $taux = $row['tauxInteret'];
            if ($taux < 3) $distribution['0-3%']++;
            elseif ($taux < 5) $distribution['3-5%']++;
            elseif ($taux < 8) $distribution['5-8%']++;
            elseif ($taux < 12) $distribution['8-12%']++;
            else $distribution['12%+']++;
        }

        return $distribution;
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
