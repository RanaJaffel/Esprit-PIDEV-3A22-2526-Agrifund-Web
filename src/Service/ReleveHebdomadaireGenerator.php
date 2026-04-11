<?php

namespace App\Service;

use App\Entity\ReleveHebdomadaire;
use Doctrine\ORM\EntityManagerInterface;

class ReleveHebdomadaireGenerator
{
    public function __construct(private EntityManagerInterface $em) {}

    public function generateForProjectWeek(int $idproject, \DateTimeInterface $weekStart): int
    {
        $conn = $this->em->getConnection();

        // ✅ On force des DateTime mutables (évite ton bug DateTimeImmutable)
        $start = new \DateTime($weekStart->format('Y-m-d'));
        $end   = (new \DateTime($weekStart->format('Y-m-d')))->modify('+6 days');

        // 1) supprimer si déjà généré pour même projet + même date_debut
        $conn->executeStatement(
            'DELETE FROM releve_hebdomadaire WHERE idproject = :p AND date_debut = :d',
            ['p' => $idproject, 'd' => $start->format('Y-m-d')]
        );

        // 2) récupérer agrégation hebdo depuis rapport_journalier
        // moyenne pondérée: SUM(moyenne*nb_mesures)/SUM(nb_mesures)
        $sql = "
            SELECT
                type_mesure,
                SUM(moyenne * nb_mesures) / NULLIF(SUM(nb_mesures),0) AS avg_week,
                SUM(nb_mesures) AS total_measures
            FROM rapport_journalier
            WHERE idproject = :p
              AND date_rapport >= :start
              AND date_rapport <= :end
            GROUP BY type_mesure
        ";

        $rows = $conn->fetchAllAssociative($sql, [
            'p' => $idproject,
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
        ]);

        // 3) condition dominante (priorité “pire alerte”)
        $condRows = $conn->fetchAllAssociative("
            SELECT condition_dominante, COUNT(*) AS cnt
            FROM rapport_journalier
            WHERE idproject = :p
              AND date_rapport >= :start
              AND date_rapport <= :end
            GROUP BY condition_dominante
        ", [
            'p' => $idproject,
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
        ]);

        $condition = $this->computeWeeklyCondition($condRows);

        // 4) extraire temp/humidité
        $tempAvg = null;
        $humAvg = null;
        $totalMeasures = 0;

        foreach ($rows as $r) {
            $type = (string)$r['type_mesure'];
            $avg  = $r['avg_week'] !== null ? (float)$r['avg_week'] : null;
            $tot  = (int)$r['total_measures'];

            $totalMeasures += $tot;

            if ($type === 'TEMPERATURE') {
                $tempAvg = $avg;
            }
            if ($type === 'HUMIDITE_SOL') {
                $humAvg = $avg;
            }
        }

        // 5) enregistrer en DB
        $hebdo = new ReleveHebdomadaire();
        $hebdo->setIdproject($idproject);
        $hebdo->setDateDebut($start);
        $hebdo->setDateFin($end);
        $hebdo->setTempMoyenne($tempAvg ?? 0);
        $hebdo->setHumiditeMoyenne($humAvg ?? 0);
        $hebdo->setNbMesuresTotal($totalMeasures);
        $hebdo->setConditionDominante($condition);
        $hebdo->setDateGeneration(new \DateTime());

        $this->em->persist($hebdo);
        $this->em->flush();

        return 1;
    }

    private function computeWeeklyCondition(array $condRows): string
    {
        $found = [];
        foreach ($condRows as $row) {
            $found[(string)$row['condition_dominante']] = (int)$row['cnt'];
        }

        $priority = [
            'ALERTE_SECHERESSE',
            'ALERTE_CHALEUR',
            'ALERTE_EXCES_EAU',
            'ALERTE_VENT',
            'ALERTE_FROID',
        ];

        foreach ($priority as $p) {
            if (isset($found[$p])) return $p;
        }

        return 'NORMAL';
    }
}