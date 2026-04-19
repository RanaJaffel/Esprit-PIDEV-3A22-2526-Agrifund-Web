<?php

namespace App\Repository;

use App\Entity\Banque;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class BanqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Banque::class);
    }
    public function findAllWithUsers(): array
    {
        return $this->createQueryBuilder('b')
            ->join('b.utilisateur', 'u')
            ->addSelect('u')
            ->orderBy('u.dateInscrit', 'DESC')
            ->getQuery()
            ->getResult();
    }
    public function searchBanques(?string $search = null, ?string $status = null): QueryBuilder
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

        return $qb;
    }
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
    public function findOneByCode(string $code): ?Banque
    {
        return $this->createQueryBuilder('b')
            ->where('b.codebanque = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }
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
