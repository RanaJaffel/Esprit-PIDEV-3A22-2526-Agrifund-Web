<?php

namespace App\Repository;

use App\Entity\RapportJournalier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RapportJournalierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RapportJournalier::class);
    }

    // ✅ Rapports par projet
    public function findByProject(int $idproject): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.idproject = :idproject')
            ->setParameter('idproject', $idproject)
            ->orderBy('r.dateRapport', 'DESC')
            ->addOrderBy('r.typeMesure', 'ASC')
            ->addOrderBy('r.idCapteur', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // ✅ Rapport d'une date spécifique par projet (tous types)
    public function findByProjectAndDate(int $idproject, \DateTimeInterface $date): array
    {
        $dateObj = \DateTimeImmutable::createFromInterface($date);

        return $this->createQueryBuilder('r')
            ->where('r.idproject = :idproject')
            ->andWhere('r.dateRapport = :date')
            ->setParameter('idproject', $idproject)
            ->setParameter('date', $dateObj)
            ->orderBy('r.typeMesure', 'ASC')
            ->addOrderBy('r.idCapteur', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // ✅ Rapport d'une date + projet + type (filtre)
    public function findByProjectDateAndType(int $idproject, \DateTimeInterface $date, string $typeMesure): array
    {
        $dateObj = \DateTimeImmutable::createFromInterface($date);

        return $this->createQueryBuilder('r')
            ->where('r.idproject = :idproject')
            ->andWhere('r.dateRapport = :date')
            ->andWhere('r.typeMesure = :type')
            ->setParameter('idproject', $idproject)
            ->setParameter('date', $dateObj)
            ->setParameter('type', $typeMesure)
            ->orderBy('r.typeMesure', 'ASC')
            ->addOrderBy('r.idCapteur', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // ✅ Rapports des 7 derniers jours par projet
    public function findLastWeekByProject(int $idproject): array
    {
        $dateDebut = new \DateTimeImmutable('-7 days');

        return $this->createQueryBuilder('r')
            ->where('r.idproject = :idproject')
            ->andWhere('r.dateRapport >= :dateDebut')
            ->setParameter('idproject', $idproject)
            ->setParameter('dateDebut', $dateDebut)
            ->orderBy('r.dateRapport', 'DESC')
            ->addOrderBy('r.typeMesure', 'ASC')
            ->addOrderBy('r.idCapteur', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // ✅ Rapport aujourd'hui par capteur et type
    public function findTodayByCapteurAndType(int $idCapteur, string $typeMesure): ?RapportJournalier
    {
        $today = new \DateTimeImmutable('today');

        return $this->createQueryBuilder('r')
            ->where('r.idCapteur = :idCapteur')
            ->andWhere('r.typeMesure = :typeMesure')
            ->andWhere('r.dateRapport = :today')
            ->setParameter('idCapteur', $idCapteur)
            ->setParameter('typeMesure', $typeMesure)
            ->setParameter('today', $today)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // ✅ Liste des types disponibles pour un projet (filtre)
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

    // ✅ Tous les types (admin filtre global)
    public function findAllTypes(): array
    {
        $rows = $this->createQueryBuilder('r')
            ->select('DISTINCT r.typeMesure AS type')
            ->orderBy('r.typeMesure', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return array_map(fn($x) => $x['type'], $rows);
    }

    // ✅ Admin : filtres (projet, date, type)
    public function findAllByFilters(?int $idproject = null, ?\DateTimeInterface $date = null, ?string $typeMesure = null, int $limit = 800): array
    {
        $qb = $this->createQueryBuilder('r')
            ->orderBy('r.dateRapport', 'DESC')
            ->addOrderBy('r.idproject', 'DESC')
            ->addOrderBy('r.typeMesure', 'ASC')
            ->setMaxResults($limit);

        if ($idproject !== null) {
            $qb->andWhere('r.idproject = :idproject')
               ->setParameter('idproject', $idproject);
        }

        if ($date !== null) {
            $qb->andWhere('r.dateRapport = :date')
               ->setParameter('date', \DateTimeImmutable::createFromInterface($date));
        }

        if ($typeMesure !== null && $typeMesure !== '') {
            $qb->andWhere('r.typeMesure = :type')
               ->setParameter('type', $typeMesure);
        }

        return $qb->getQuery()->getResult();
    }
}