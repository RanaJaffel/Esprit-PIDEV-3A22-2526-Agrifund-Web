<?php

namespace App\Repository;

use App\Entity\Agriculteur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Agriculteur>
 */
class AgriculteurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agriculteur::class);
    }

    /**
     * Trouve tous les agriculteurs avec leurs utilisateurs
     */
    public function findAllWithUsers(): array
    {
        return $this->createQueryBuilder('a')
            ->join('a.utilisateur', 'u')
            ->orderBy('u.dateInscrit', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche d'agriculteurs
     */
    public function searchAgriculteurs(?string $search = null, ?string $status = null)
    {
        $qb = $this->createQueryBuilder('a')
            ->join('a.utilisateur', 'u')
            ->orderBy('u.dateInscrit', 'DESC');

        if ($search) {
            $qb->andWhere('u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search OR a.typeCulture LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($status) {
            $qb->andWhere('a.statuscompte = :status')
                ->setParameter('status', $status);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Agriculteurs en attente de vérification
     */
    public function findPendingVerification(): array
    {
        return $this->createQueryBuilder('a')
            ->join('a.utilisateur', 'u')
            ->where('a.statuscompte = :status')
            ->setParameter('status', 'en_attente')
            ->orderBy('u.dateInscrit', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques des agriculteurs
     */
    public function getStatistics(): array
    {
        $total = (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $actifs = (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.statuscompte = :status')
            ->setParameter('status', 'actif')
            ->getQuery()
            ->getSingleScalarResult();

        $enAttente = (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.statuscompte = :status')
            ->setParameter('status', 'en_attente')
            ->getQuery()
            ->getSingleScalarResult();

        $rejetes = (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.statuscompte = :status')
            ->setParameter('status', 'refuse')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total' => $total,
            'actifs' => $actifs,
            'en_attente' => $enAttente,
            'rejetes' => $rejetes,
        ];
    }

    /**
     * Agriculteurs par type de culture
     */
    public function countByTypeCulture(): array
    {
        return $this->createQueryBuilder('a')
            ->select('a.typeCulture, COUNT(a.id) as nombre')
            ->groupBy('a.typeCulture')
            ->getQuery()
            ->getResult();
    }
}