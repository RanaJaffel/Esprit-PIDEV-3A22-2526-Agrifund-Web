<?php

namespace App\Command;

use App\Entity\Agriculteur;
use App\Entity\Banque;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-users',
    description: 'Crée des utilisateurs de test (admin, agriculteur et banque).',
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
        // L'utilisation de TRUNCATE est rapide mais peut échouer avec les contraintes de clé étrangère.
        // Nous désactivons les vérifications de clés étrangères pour nous assurer que cela fonctionne.
        // C'est généralement sûr pour une commande de seeding qui reconstruit les données.
        // NOTE : Cette syntaxe est spécifique à MySQL/MariaDB.
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0;');
        $platform = $connection->getDatabasePlatform();
        $connection->executeStatement($platform->getTruncateTableSQL($this->entityManager->getClassMetadata(Utilisateur::class)->getTableName(), true));
        $connection->executeStatement($platform->getTruncateTableSQL($this->entityManager->getClassMetadata(Agriculteur::class)->getTableName(), true));
        $connection->executeStatement($platform->getTruncateTableSQL($this->entityManager->getClassMetadata(Banque::class)->getTableName(), true));
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1;');

        // Create Admin User
        $io->writeln("\n👤 Creating admin user...");
        $admin = new Utilisateur();
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

        // Create Agriculteur User
        $io->writeln("\n👤 Creating agriculteur user...");
        $agriculteurUser = new Utilisateur();
        $agriculteurUser->setEmail('agri@agrifund.com');
        $agriculteurUser->setFirstname('Agri');
        $agriculteurUser->setLastname('Culteur');
        $agriculteurUser->setRoles(['ROLE_AGRICULTEUR']);
        $agriculteurUser->setPassword($this->passwordHasher->hashPassword($agriculteurUser, 'agri123'));
        $agriculteurUser->setIsActive(true);

        $agriculteur = new Agriculteur();
        $agriculteur->setUtilisateur($agriculteurUser);
        $agriculteurUser->setAgriculteur($agriculteur);

        $this->entityManager->persist($agriculteurUser);
        $this->entityManager->persist($agriculteur);
        $io->writeln('  ✓ Agriculteur User Created:');
        $io->writeln('     Email: agri@agrifund.com');
        $io->writeln('     Password: agri123');
        $io->writeln('     Role: ROLE_AGRICULTEUR');

        // Create Banque User
        $io->writeln("\n👤 Creating banque user...");
        $banqueUser = new Utilisateur();
        $banqueUser->setEmail('banque@agrifund.com');
        $banqueUser->setFirstname('Banque');
        $banqueUser->setLastname('Agri');
        $banqueUser->setRoles(['ROLE_BANQUE']);
        $banqueUser->setPassword($this->passwordHasher->hashPassword($banqueUser, 'banque123'));
        $banqueUser->setIsActive(true);

        $banque = new Banque();
        $banque->setUtilisateur($banqueUser);
        $banqueUser->setBanque($banque);

        $this->entityManager->persist($banqueUser);
        $this->entityManager->persist($banque);
        $io->writeln('  ✓ Banque User Created:');
        $io->writeln('     Email: banque@agrifund.com');
        $io->writeln('     Password: banque123');
        $io->writeln('     Role: ROLE_BANQUE');

        $this->entityManager->flush();

        $io->success('✅ Users created successfully!');
        $io->section('Login Credentials');
        $io->table(
            ['User Type', 'Email', 'Password', 'Role'],
            [
                ['Admin', 'admin@agrifund.com', 'admin123', 'ROLE_ADMIN, ROLE_USER'],
                ['Agriculteur', 'agri@agrifund.com', 'agri123', 'ROLE_AGRICULTEUR, ROLE_USER'],
                ['Banque', 'banque@agrifund.com', 'banque123', 'ROLE_BANQUE, ROLE_USER'],
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
