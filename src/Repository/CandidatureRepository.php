<?php

namespace App\Repository;

use App\Entity\Candidature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Candidature>
 *
 * @method Candidature|null find($id, $lockMode = null, $lockVersion = null)
 * @method Candidature|null findOneBy(array $criteria, array $orderBy = null)
 * @method Candidature[]    findAll()
 * @method Candidature[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CandidatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidature::class);
    }

    public function findByFarmer(int $farmerId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.agriculteur = :val')
            ->setParameter('val', $farmerId)
            ->orderBy('c.dateDepot', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByFarmerAndOffer(int $farmerId, int $offerId): ?Candidature
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.agriculteur = :farmer')
            ->andWhere('c.offre = :offer')
            ->setParameter('farmer', $farmerId)
            ->setParameter('offer', $offerId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
