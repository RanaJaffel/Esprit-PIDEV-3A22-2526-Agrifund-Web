<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TransactionPaiement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TransactionPaiement>
 */
class TransactionPaiementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransactionPaiement::class);
    }

    public function findOneByReference(string $reference): ?TransactionPaiement
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.reference = :reference')
            ->setParameter('reference', $reference)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByProviderEventId(string $providerEventId): ?TransactionPaiement
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.providerEventId = :providerEventId')
            ->setParameter('providerEventId', $providerEventId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByProviderPaymentId(string $providerPaymentId): ?TransactionPaiement
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.providerPaymentId = :providerPaymentId')
            ->setParameter('providerPaymentId', $providerPaymentId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function createAdminSearchQueryBuilder(
        ?string $status = null,
        ?string $method = null,
        ?string $provider = null
    ): QueryBuilder {
        $queryBuilder = $this->createQueryBuilder('t')
            ->leftJoin('t.achat', 'a')
            ->leftJoin('a.utilisateur', 'u')
            ->addSelect('a', 'u')
            ->orderBy('t.id', 'DESC');

        if ($status !== null && $status !== '') {
            $queryBuilder
                ->andWhere('t.statut = :status')
                ->setParameter('status', $status);
        }

        if ($method !== null && $method !== '') {
            $queryBuilder
                ->andWhere('t.paymentMethod = :method')
                ->setParameter('method', $method);
        }

        if ($provider !== null && $provider !== '') {
            $queryBuilder
                ->andWhere('t.provider = :provider')
                ->setParameter('provider', $provider);
        }

        return $queryBuilder;
    }
}
