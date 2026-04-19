<?php

namespace App\Repository;

use App\Entity\Banque;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
            ->orderBy('u.dateInscrit', 'DESC')
            ->getQuery()
            ->getResult();
    }
    public function searchBanques(?string $search = null, ?string $status = null)
    {
        $qb = $this->createQueryBuilder('b')
            ->join('b.utilisateur', 'u')
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
    public function findPendingVerification(): array
    {
        return $this->createQueryBuilder('b')
            ->join('b.utilisateur', 'u')
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
        $total = (int) $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $actives = (int) $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->where('b.statusCompte = :status')
            ->setParameter('status', 'actif')
            ->getQuery()
            ->getSingleScalarResult();

        $enAttente = (int) $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->where('b.statusCompte = :status')
            ->setParameter('status', 'en_attente')
            ->getQuery()
            ->getSingleScalarResult();

        $rejetees = (int) $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->where('b.statusCompte = :status')
            ->setParameter('status', 'refuse')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total' => $total,
            'actives' => $actives,
            'en_attente' => $enAttente,
            'rejetees' => $rejetees,
        ];
    }
}