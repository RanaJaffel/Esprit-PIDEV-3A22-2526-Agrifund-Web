<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-users',
    description: 'Create test users (admin and client)',
)]
class CreateUsersCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Creating Test Users');

        // Clear existing users
        $io->writeln('🗑️  Clearing existing users...');
        $this->entityManager->getConnection()->executeStatement('DELETE FROM users');

        // Create Admin User
        $io->writeln("\n👤 Creating admin user...");
        $admin = new User();
        $admin->setEmail('admin@agrifund.com');
        $admin->setFirstname('Admin');
        $admin->setLastname('User');
        $admin->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $admin->setIsActive(true);

        $this->entityManager->persist($admin);
        $io->writeln('  ✓ Admin User Created:');
        $io->writeln('     Email: admin@agrifund.com');
        $io->writeln('     Password: admin123');
        $io->writeln('     Role: ROLE_ADMIN');

        // Create Client User
        $io->writeln("\n👤 Creating client user...");
        $client = new User();
        $client->setEmail('client@agrifund.com');
        $client->setFirstname('Client');
        $client->setLastname('User');
        $client->setRoles(['ROLE_USER']);
        $client->setPassword($this->passwordHasher->hashPassword($client, 'client123'));
        $client->setIsActive(true);

        $this->entityManager->persist($client);
        $io->writeln('  ✓ Client User Created:');
        $io->writeln('     Email: client@agrifund.com');
        $io->writeln('     Password: client123');
        $io->writeln('     Role: ROLE_USER');

        $this->entityManager->flush();

        $io->success('✅ Users created successfully!');
        $io->section('Login Credentials');
        $io->table(
            ['User Type', 'Email', 'Password', 'Role'],
            [
                ['Admin', 'admin@agrifund.com', 'admin123', 'ROLE_ADMIN'],
                ['Client', 'client@agrifund.com', 'client123', 'ROLE_USER'],
            ]
        );

        $io->section('Access Points');
        $io->writeln([
            '🔗 Login Page: http://localhost:8000/login',
            '🔐 Admin Panel: http://localhost:8000/admin/produits/',
            '📦 Products: http://localhost:8000/admin/produits/',
            '🎁 Offers: http://localhost:8000/admin/offres/',
        ]);

        return Command::SUCCESS;
    }
}
