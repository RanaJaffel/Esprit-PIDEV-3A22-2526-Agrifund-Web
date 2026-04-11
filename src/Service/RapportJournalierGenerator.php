<?php

namespace App\Service;

use App\Entity\RapportJournalier;
use Doctrine\ORM\EntityManagerInterface;

class RapportJournalierGenerator
{
    public function __construct(private EntityManagerInterface $em) {}

    public function generateForProjectDate(int $idproject, \DateTimeInterface $date): int
    {
        $conn = $this->em->getConnection();

        // On travaille avec une journée [00:00, 00:00+1j)
        $dayStart = (new \DateTimeImmutable($date->format('Y-m-d')))->setTime(0, 0, 0);
        $dayEnd   = $dayStart->modify('+1 day');

        // 1) Regénération propre : supprimer les rapports existants (projet + date)
        $conn->executeStatement(
            'DELETE FROM rapport_journalier WHERE idproject = :p AND date_rapport = :d',
            [
                'p' => $idproject,
                'd' => $dayStart->format('Y-m-d'),
            ]
        );

        // 2) Agrégation depuis releve_terrain (en excluant ERREUR_CAPTEUR)
        $sql = "
            SELECT
                r.idproject,
                r.id_capteur,
                r.type_mesure,
                DATE(r.date_heure) AS date_rapport,
                ROUND(AVG(r.valeur_mesuree), 2) AS moyenne,
                ROUND(MIN(r.valeur_mesuree), 2) AS min_val,
                ROUND(MAX(r.valeur_mesuree), 2) AS max_val,
                COUNT(*) AS nb_mesures
            FROM releve_terrain r
            WHERE r.idproject = :p
              AND r.date_heure >= :start
              AND r.date_heure < :end
              AND (r.qualite IS NULL OR r.qualite <> 'ERREUR_CAPTEUR')
            GROUP BY r.idproject, r.id_capteur, r.type_mesure, DATE(r.date_heure)
        ";

        $rows = $conn->fetchAllAssociative($sql, [
            'p'     => $idproject,
            'start' => $dayStart->format('Y-m-d H:i:s'),
            'end'   => $dayEnd->format('Y-m-d H:i:s'),
        ]);

        $count = 0;

        foreach ($rows as $row) {
            $rapport = new RapportJournalier();

            $rapport->setIdproject((int)$row['idproject']);
            $rapport->setIdCapteur((int)$row['id_capteur']);
            $rapport->setTypeMesure((string)$row['type_mesure']);

            // ✅ IMPORTANT : Doctrine DateType chez toi attend DateTime (mutable)
            $dateRapport = \DateTime::createFromFormat('Y-m-d', $row['date_rapport']);
            $rapport->setDateRapport($dateRapport ?: new \DateTime($row['date_rapport']));

            $rapport->setMoyenne((float)$row['moyenne']);
            $rapport->setMin((float)$row['min_val']);
            $rapport->setMax((float)$row['max_val']);
            $rapport->setNbMesures((int)$row['nb_mesures']);

            $rapport->setConditionDominante(
                $this->computeCondition((string)$row['type_mesure'], (float)$row['moyenne'])
            );

            $this->em->persist($rapport);
            $count++;
        }

        $this->em->flush();

        return $count;
    }

    private function computeCondition(string $typeMesure, float $moy): string
    {
        return match ($typeMesure) {
            'TEMPERATURE'  => ($moy >= 35 ? 'ALERTE_CHALEUR' : ($moy <= 5 ? 'ALERTE_FROID' : 'NORMAL')),
            'HUMIDITE_SOL' => ($moy <= 30 ? 'ALERTE_SECHERESSE' : ($moy >= 85 ? 'ALERTE_EXCES_EAU' : 'NORMAL')),
            'VENT'         => ($moy >= 40 ? 'ALERTE_VENT' : 'NORMAL'),
            default        => 'NORMAL',
        };
    }
}