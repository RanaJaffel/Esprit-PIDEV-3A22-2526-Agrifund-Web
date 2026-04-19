<?php

namespace App\Command;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:list-users',
    description: 'List all users in the database',
)]
class ListUsersCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $users = $this->entityManager->getRepository(Utilisateur::class)->findAll();

        if (empty($users)) {
            $output->writeln('<comment>No users found in the database.</comment>');
            return Command::SUCCESS;
        }

        $table = new Table($output);
        $table->setHeaders(['ID', 'Email', 'First Name', 'Last Name', 'Roles', 'Active', 'Created At']);

        foreach ($users as $user) {
            $table->addRow([
                $user->getId(),
                $user->getEmail(),
                $user->getFirstname(),
                $user->getLastname(),
                implode(', ', $user->getRoles()),
                $user->isIsActive() ? 'Yes' : 'No',
                $user->getCreatedAt()->format('Y-m-d H:i:s'),
            ]);
        }

        $table->render();

        $output->writeln('');
        $output->writeln('<info>Total users: ' . count($users) . '</info>');

        return Command::SUCCESS;
    }
}
