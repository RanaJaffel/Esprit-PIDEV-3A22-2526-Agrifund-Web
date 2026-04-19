<?php

namespace App\Command;

use App\Repository\ReleveTerrainRepository;
use App\Service\IoTAiClient;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'iot:ai-scan')]
class IotAiScanCommand extends Command
{
    public function __construct(
        private ReleveTerrainRepository $releveRepo,
        private IoTAiClient $ai,
        private NotificationService $notif,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // on analyse les mesures OK récentes (ex: 2 dernières heures)
        $since = (new \DateTimeImmutable())->modify('-2 hours');
        $measures = $this->releveRepo->findRecentOkMeasures($since, 300);

        $changed = 0;

        foreach ($measures as $m) {
            $pid = (int) $m->getIdproject();
            $cid = (int) $m->getIdCapteur();
            $type = (string) $m->getTypeMesure();
            $value = (float) $m->getValeurMesuree();

            // historique 24h du même capteur/type
            $historySince = (new \DateTimeImmutable())->modify('-24 hours');
            $historyValues = $this->releveRepo->findHistoryValues($pid, $cid, $type, $historySince, 250);

            // éviter de tester si historique trop petit
            if (count($historyValues) < 20) {
                continue;
            }

            $res = $this->ai->detectAnomaly($pid, $cid, $type, $historyValues, $value);

            if (($res['isAnomaly'] ?? false) === true) {
                $m->setQualite('SUSPECT');

                $note = sprintf(
                    "ANOMALIE_IA (%s) score=%s",
                    $res['reason'] ?? 'outlier',
                    $res['anomalyScore'] ?? 'n/a'
                );
                $m->setNote($note);

                // notification dédupliquée
                $this->notif->createOnce(
                    $pid,
                    $cid,
                    'ANOMALIE_IA',
                    'WARN',
                    "Projet #{$pid} — Capteur #{$cid} — {$type}={$value}{$m->getUnite()} => {$note}",
                    2
                );

                $changed++;
            }
        }

        $this->em->flush();
        $output->writeln("OK: IA scan terminé, suspects ajoutés = {$changed}");

        return Command::SUCCESS;
    }
}