<?php

namespace App\Command;

use App\Service\SensorAiAnalysisService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:ai:scan')]
class AiAnalyzeCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private SensorAiAnalysisService $analysisService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $conn = $this->em->getConnection();

        $rows = $conn->fetchAllAssociative("
            SELECT *
            FROM releve_terrain
            ORDER BY date_heure DESC
            LIMIT 20
        ");

        foreach ($rows as $row) {

            $this->analysisService->analyze(
                $row['idproject'],
                $row['id_capteur'],
                $row['type_mesure'],
                (float)$row['valeur_mesuree']
            );

            $output->writeln("Analyzed sensor " . $row['id_capteur']);
        }

        return Command::SUCCESS;
    }
}