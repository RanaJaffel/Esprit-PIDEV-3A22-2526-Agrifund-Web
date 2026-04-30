<?php

namespace App\Repository;

use App\Entity\ReleveTerrain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReleveTerrainRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReleveTerrain::class);
    }

    public function findByProject(int $idproject): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.idproject = :idproject')
            ->setParameter('idproject', $idproject)
            ->orderBy('r.dateHeure', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByCapteur(int $idCapteur): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.idCapteur = :idCapteur')
            ->setParameter('idCapteur', $idCapteur)
            ->orderBy('r.dateHeure', 'DESC')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }

    public function findTodayByProject(int $idproject): array
    {
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        return $this->createQueryBuilder('r')
            ->where('r.idproject = :idproject')
            ->andWhere('r.dateHeure >= :today')
            ->andWhere('r.dateHeure < :tomorrow')
            ->setParameter('idproject', $idproject)
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->orderBy('r.dateHeure', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findDernieresMesures(int $idproject, int $limit = 20): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.idproject = :idproject')
            ->setParameter('idproject', $idproject)
            ->orderBy('r.dateHeure', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function supprimerAnciennes(): int
    {
        $date = new \DateTime('-7 days');

        return $this->createQueryBuilder('r')
            ->delete()
            ->where('r.dateHeure < :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->execute();
    }

    public function findByProjectAndType(int $idproject, string $typeMesure): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.idproject = :idproject')
            ->andWhere('r.typeMesure = :type')
            ->setParameter('idproject', $idproject)
            ->setParameter('type', $typeMesure)
            ->orderBy('r.dateHeure', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findTypesByProject(int $idproject): array
    {
        $rows = $this->createQueryBuilder('r')
            ->select('DISTINCT r.typeMesure AS type')
            ->where('r.idproject = :idproject')
            ->setParameter('idproject', $idproject)
            ->orderBy('r.typeMesure', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return array_map(fn($x) => $x['type'], $rows);
    }

    public function findAllRecent(int $limit = 500): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.dateHeure', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findAllTypes(): array
    {
        $rows = $this->createQueryBuilder('r')
            ->select('DISTINCT r.typeMesure AS type')
            ->orderBy('r.typeMesure', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return array_map(fn($x) => $x['type'], $rows);
    }

    public function findSuspectsSince(\DateTimeImmutable $since, int $limit = 300): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.qualite = :q')
            ->andWhere('r.dateHeure >= :since')
            ->setParameter('q', 'SUSPECT')
            ->setParameter('since', $since)
            ->orderBy('r.dateHeure', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByProjectFilters(
        int $idproject,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
        string $type = '',
        string $qualite = ''
    ): array {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.idproject = :pid')
            ->andWhere('r.dateHeure BETWEEN :from AND :to')
            ->setParameter('pid', $idproject)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->orderBy('r.dateHeure', 'DESC');

        if ($type !== '') {
            $qb->andWhere('r.typeMesure = :t')->setParameter('t', $type);
        }
        if ($qualite !== '') {
            $qb->andWhere('r.qualite = :q')->setParameter('q', $qualite);
        }

        return $qb->getQuery()->getResult();
    }

    public function findHistoryValues(
        int $idproject,
        int $idCapteur,
        string $typeMesure,
        \DateTimeImmutable $since,
        int $limit = 200
    ): array {
        $rows = $this->createQueryBuilder('r')
            ->select('r.valeurMesuree AS v')
            ->andWhere('r.idproject = :pid')
            ->andWhere('r.idCapteur = :cid')
            ->andWhere('r.typeMesure = :tm')
            ->andWhere('r.dateHeure >= :since')
            ->andWhere('r.qualite IN (:q)')
            ->setParameter('pid', $idproject)
            ->setParameter('cid', $idCapteur)
            ->setParameter('tm', $typeMesure)
            ->setParameter('since', $since)
            ->setParameter('q', ['OK', 'SUSPECT'])
            ->orderBy('r.dateHeure', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return array_map(fn($r) => (float) $r['v'], $rows);
    }

    public function findRecentOkMeasures(\DateTimeImmutable $since, int $limit = 200): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.dateHeure >= :since')
            ->andWhere('r.qualite = :q')
            ->setParameter('since', $since)
            ->setParameter('q', 'OK')
            ->orderBy('r.dateHeure', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findLast24Hours(int $idproject): array
{
    return $this->createQueryBuilder('r')
        ->where('r.idproject = :idproject')
        ->andWhere('r.dateHeure >= :since')
        ->setParameter('idproject', $idproject)
        ->setParameter('since', new \DateTimeImmutable('-24 hours'))
        ->orderBy('r.dateHeure', 'DESC')
        ->getQuery()
        ->getResult();
}

public function findLatestByProject(int $idproject): ?ReleveTerrain
{
    return $this->createQueryBuilder('r')
        ->where('r.idproject = :idproject')
        ->setParameter('idproject', $idproject)
        ->orderBy('r.dateHeure', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
}

}