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