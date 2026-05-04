<?php

namespace App\Repository;

use App\Entity\Document;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Document>
 */
class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    /**
     * Trouve les documents d'un utilisateur
     *
     * @return Document[]
     */
    public function findByUtilisateur(Utilisateur $utilisateur): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.utilisateur = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->orderBy('d.dateUpload', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Document[]
     */
    public function findRecentByUtilisateur(Utilisateur $utilisateur, int $limit = 5): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.utilisateur = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->orderBy('d.dateUpload', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array{total: int, valides: int, en_attente: int, rejetes: int}
     */
    public function getDashboardStatsForUtilisateur(Utilisateur $utilisateur): array
    {
        $row = $this->createQueryBuilder('d')
            ->select('COUNT(d.id) AS total')
            ->addSelect("SUM(CASE WHEN d.statut = 'valide' THEN 1 ELSE 0 END) AS valides")
            ->addSelect("SUM(CASE WHEN d.statut = 'en_attente' THEN 1 ELSE 0 END) AS enAttente")
            ->addSelect("SUM(CASE WHEN d.statut = 'rejete' THEN 1 ELSE 0 END) AS rejetes")
            ->where('d.utilisateur = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->getQuery()
            ->getSingleResult();

        return [
            'total' => (int) ($row['total'] ?? 0),
            'valides' => (int) ($row['valides'] ?? 0),
            'en_attente' => (int) ($row['enAttente'] ?? 0),
            'rejetes' => (int) ($row['rejetes'] ?? 0),
        ];
    }

    /**
     * Recherche de documents (pour admin)
     */
    public function searchDocuments(?string $search = null, ?string $statut = null, ?string $type = null)
    {
        $qb = $this->createQueryBuilder('d')
            ->join('d.utilisateur', 'u')
            ->orderBy('d.dateUpload', 'DESC');

        if ($search) {
            $qb->andWhere('d.nom LIKE :search OR u.nom LIKE :search OR u.prenom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($statut) {
            $qb->andWhere('d.statut = :statut')
                ->setParameter('statut', $statut);
        }

        if ($type) {
            $qb->andWhere('d.typeDocument = :type')
                ->setParameter('type', $type);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Documents en attente de validation
     */
    public function findPendingDocuments(): array
    {
        return $this->createQueryBuilder('d')
            ->join('d.utilisateur', 'u')
            ->where('d.statut = :statut')
            ->setParameter('statut', 'en_attente')
            ->orderBy('d.dateUpload', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les documents par statut
     */
    public function countByStatut(): array
    {
        $result = $this->createQueryBuilder('d')
            ->select('d.statut, COUNT(d.id) as nombre')
            ->groupBy('d.statut')
            ->getQuery()
            ->getResult();

        $counts = [
            'en_attente' => 0,
            'valide' => 0,
            'rejete' => 0,
        ];

        foreach ($result as $row) {
            $counts[$row['statut']] = (int) $row['nombre'];
        }

        return $counts;
    }

    /**
     * Documents expirés ou expirant bientôt
     */
    public function findExpiringDocuments(int $days = 30): array
    {
        $date = new \DateTime();
        $date->modify("+{$days} days");

        return $this->createQueryBuilder('d')
            ->join('d.utilisateur', 'u')
            ->where('d.dateExpiration IS NOT NULL')
            ->andWhere('d.dateExpiration <= :date')
            ->andWhere('d.statut = :statut')
            ->setParameter('date', $date)
            ->setParameter('statut', 'valide')
            ->orderBy('d.dateExpiration', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques des documents
     */
    public function getStatistics(): array
    {
        $total = (int) $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $byStatus = $this->countByStatut();

        $byType = $this->createQueryBuilder('d')
            ->select('d.typeDocument, COUNT(d.id) as nombre')
            ->groupBy('d.typeDocument')
            ->getQuery()
            ->getResult();

        return [
            'total' => $total,
            'statuts' => $byStatus,
            'types' => $byType,
        ];
    }
}
