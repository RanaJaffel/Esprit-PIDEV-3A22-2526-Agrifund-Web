<?php

namespace App\Command;

use App\Repository\CapteurRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'iot:check-sensors')]
class CheckSensorsCommand extends Command
{
    public function __construct(
        private CapteurRepository $capteurRepo,
        private NotificationService $notif,
        private EntityManagerInterface $em
    ) { parent::__construct(); }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = new \DateTimeImmutable();
        $capteurs = $this->capteurRepo->findBy(['statut' => 'ACTIF']);

        foreach ($capteurs as $c) {
            $last = $c->getLastSeenAt();
            if (!$last) continue;

            $mins = (int) floor(($now->getTimestamp() - $last->getTimestamp()) / 60);

            if ($mins > 360) {
                $c->setStatut('EN_PANNE');
                $this->notif->createOnce(
                    $c->getIdproject(),
                    $c->getIdCapteur(),
                    'CAPTEUR_EN_PANNE',
                    'CRITICAL',
                    "Capteur #{$c->getIdCapteur()} en panne : {$mins} min sans données",
                    6
                );
            } elseif ($mins > 90) {
                $this->notif->createOnce(
                    $c->getIdproject(),
                    $c->getIdCapteur(),
                    'CAPTEUR_MUET',
                    'WARN',
                    "Capteur #{$c->getIdCapteur()} muet : {$mins} min sans données",
                    2
                );
            }
        }

        $this->em->flush();
        $output->writeln("OK: check capteurs terminé");
        return Command::SUCCESS;
    }
}