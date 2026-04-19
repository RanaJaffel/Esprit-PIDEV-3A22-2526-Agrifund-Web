<?php

namespace App\Repository;

use App\Entity\BlockchainRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BlockchainRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlockchainRecord::class);
    }

    /**
     * Récupère tous les blocs par ordre chronologique
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Dernier bloc de la chaîne
     */
    public function findLastBlock(): ?BlockchainRecord
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Nombre total de blocs
     */
    public function getTotalBlocks(): int
    {
        return $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Blocs récents (N derniers)
     */
    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche par hash
     */
    public function findByHash(string $hash): ?BlockchainRecord
    {
        return $this->createQueryBuilder('b')
            ->where('b.dataHash = :hash')
            ->setParameter('hash', $hash)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Statistiques blockchain
     */
    public function getStats(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT 
                COUNT(*) as total_blocks,
                MIN(created_at) as first_block_date,
                MAX(created_at) as last_block_date,
                AVG(CHAR_LENGTH(JSON_EXTRACT(data, "$"))) as avg_data_size
            FROM blockchain_records
        ';

        return $conn->executeQuery($sql)->fetchAssociative();
    }

    /**
     * Activité par jour (7 derniers jours)
     */
    public function getActivityByDay(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as blocks_count
            FROM blockchain_records
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ';

        return $conn->executeQuery($sql)->fetchAllAssociative();
    }
}