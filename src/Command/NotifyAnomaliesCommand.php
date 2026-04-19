<?php

namespace App\Command;

use App\Repository\ReleveTerrainRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'iot:notify-anomalies')]
class NotifyAnomaliesCommand extends Command
{
    public function __construct(
        private ReleveTerrainRepository $releveRepo,
        private NotificationService $notif,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // On regarde les suspects des dernières 24h (tu peux réduire à 6h)
        $since = (new \DateTimeImmutable())->modify('-24 hours');

        $suspects = $this->releveRepo->findSuspectsSince($since, 1000);

        foreach ($suspects as $r) {
            $projetId = (int) $r->getIdproject();
            $capteurId = (int) $r->getIdCapteur();

            $note = (string) ($r->getNote() ?? 'Mesure suspecte détectée');

            // Déduire le type d’alerte depuis la note (ton simulateur met ces tags)
            $type = 'ANOMALIE_SEVERE';
            $severity = 'CRITICAL';

            if (str_contains($note, 'CAPTEUR_BLOQUE')) {
                $type = 'CAPTEUR_BLOQUE';
                $severity = 'WARN';
            }

            $msg = sprintf(
                "Projet #%d — Capteur #%d — %s (%s=%s%s)",
                $projetId,
                $capteurId,
                $note,
                $r->getTypeMesure(),
                $r->getValeurMesuree(),
                $r->getUnite()
            );

            // déduplication 2h
            $this->notif->createOnce($projetId, $capteurId, $type, $severity, $msg, 2);
        }

        $this->em->flush();
        $output->writeln("OK: notifications anomalies générées");

        return Command::SUCCESS;
    }
}