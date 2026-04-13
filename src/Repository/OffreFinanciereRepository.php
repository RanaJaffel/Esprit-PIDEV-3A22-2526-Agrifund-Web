<?php

namespace App\Repository;

use App\Entity\OffreFinanciere;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OffreFinanciere>
 */
class OffreFinanciereRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OffreFinanciere::class);
    }

    public function findActiveOffers(): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.statut = :statut')
            ->setParameter('statut', 'Active')
            ->orderBy('o.nomOffre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByProduit($produitId): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.produitFinancier = :produit')
            ->setParameter('produit', $produitId)
            ->orderBy('o.nomOffre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.nomOffre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche avancée avec filtres multiples pour les offres
     */
    public function searchAndFilter(
        ?string $keyword = null,
        ?string $statut = null,
        ?int $produitId = null,
        ?string $typeFinancement = null,
        string $sortBy = 'nomOffre',
        string $sortOrder = 'ASC'
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.produitFinancier', 'p')
            ->addSelect('p');

        if ($keyword) {
            $qb->andWhere('o.nomOffre LIKE :keyword OR o.conditions LIKE :keyword OR p.nomProduit LIKE :keyword')
               ->setParameter('keyword', '%' . $keyword . '%');
        }

        if ($statut) {
            $qb->andWhere('o.statut = :statut')
               ->setParameter('statut', $statut);
        }

        if ($produitId) {
            $qb->andWhere('o.produitFinancier = :produitId')
               ->setParameter('produitId', $produitId);
        }

        if ($typeFinancement) {
            $qb->andWhere('p.typeFinancement = :type')
               ->setParameter('type', $typeFinancement);
        }

        // Tri dynamique
        $allowedSorts = [
            'nomOffre' => 'o.nomOffre',
            'statut' => 'o.statut',
            'produit' => 'p.nomProduit',
        ];

        $sortField = $allowedSorts[$sortBy] ?? 'o.nomOffre';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        $qb->orderBy($sortField, $sortOrder);

        return $qb;
    }

    /**
     * Statistiques globales des offres (Optimisé: 1 seule requête)
     */
    public function getStatistics(): array
    {
        $stats = $this->createQueryBuilder('o')
            ->select('COUNT(o.id) as total')
            ->addSelect("SUM(CASE WHEN o.statut = 'Active' THEN 1 ELSE 0 END) as active")
            ->addSelect("SUM(CASE WHEN o.statut = 'En attente' THEN 1 ELSE 0 END) as enAttente")
            ->addSelect("SUM(CASE WHEN o.statut = 'Cancelled' THEN 1 ELSE 0 END) as cancelled")
            ->getQuery()
            ->getSingleResult();

        $total = (int)$stats['total'];
        $active = (int)$stats['active'];

        return [
            'total' => $total,
            'active' => $active,
            'enAttente' => (int)$stats['enAttente'],
            'cancelled' => (int)$stats['cancelled'],
            'tauxActivation' => $total > 0 ? round(($active / $total) * 100, 1) : 0,
        ];
    }

    /**
     * Nombre d'offres par statut
     */
    public function countByStatut(): array
    {
        return $this->createQueryBuilder('o')
            ->select('o.statut, COUNT(o.id) as total')
            ->groupBy('o.statut')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Nombre d'offres par produit financier
     */
    public function countByProduit(): array
    {
        return $this->createQueryBuilder('o')
            ->select('p.nomProduit, p.typeFinancement, COUNT(o.id) as totalOffres')
            ->leftJoin('o.produitFinancier', 'p')
            ->groupBy('p.id, p.nomProduit, p.typeFinancement')
            ->orderBy('totalOffres', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Offres les plus récentes (par ID décroissant)
     */
    public function findRecent(int $limit = 5): array
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.produitFinancier', 'p')
            ->addSelect('p')
            ->orderBy('o.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les statuts distincts existants
     */
    public function findDistinctStatuts(): array
    {
        $results = $this->createQueryBuilder('o')
            ->select('DISTINCT o.statut')
            ->orderBy('o.statut', 'ASC')
            ->getQuery()
            ->getResult();

        return array_column($results, 'statut');
    }
}
