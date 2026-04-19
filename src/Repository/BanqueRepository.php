<?php

namespace App\Repository;

use App\Entity\Banque;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Banque>
 */
class BanqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Banque::class);
    }

    /**
     * Trouve toutes les banques avec leurs utilisateurs
     */
    public function findAllWithUsers(): array
    {
        return $this->createQueryBuilder('b')
            ->join('b.utilisateur', 'u')
            ->addSelect('u')
            ->orderBy('u.dateInscrit', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche de banques
     */
    public function searchBanques(?string $search = null, ?string $status = null)
    {
        $qb = $this->createQueryBuilder('b')
            ->join('b.utilisateur', 'u')
            ->addSelect('u')
            ->orderBy('u.dateInscrit', 'DESC');

        if ($search) {
            $qb->andWhere('u.nom LIKE :search OR b.codebanque LIKE :search OR b.representantLegal LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($status) {
            $qb->andWhere('b.statusCompte = :status')
                ->setParameter('status', $status);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Banques en attente de vérification
     */
    public function findPendingVerification(): array
    {
        return $this->createQueryBuilder('b')
            ->join('b.utilisateur', 'u')
            ->addSelect('u')
            ->where('b.statusCompte = :status')
            ->setParameter('status', 'en_attente')
            ->orderBy('u.dateInscrit', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve par code banque
     */
    public function findOneByCode(string $code): ?Banque
    {
        return $this->createQueryBuilder('b')
            ->where('b.codebanque = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Statistiques des banques
     */
    public function getStatistics(): array
    {
        $rows = $this->createQueryBuilder('b')
            ->select('b.statusCompte AS status, COUNT(b.id) AS total')
            ->groupBy('b.statusCompte')
            ->getQuery()
            ->getArrayResult();

        $stats = [
            'total' => 0,
            'actives' => 0,
            'en_attente' => 0,
            'rejetees' => 0,
        ];

        foreach ($rows as $row) {
            $count = (int) ($row['total'] ?? 0);
            $status = (string) ($row['status'] ?? '');

            $stats['total'] += $count;

            if ($status === 'actif') {
                $stats['actives'] = $count;
            } elseif ($status === 'en_attente') {
                $stats['en_attente'] = $count;
            } elseif ($status === 'refuse') {
                $stats['rejetees'] = $count;
            }
        }

        return $stats;
    }
}
