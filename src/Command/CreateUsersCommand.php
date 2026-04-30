<?php

namespace App\Command;

use App\Entity\Admin;
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
    description: 'Cree des utilisateurs de test (admin, agriculteur et banque).',
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

        $io->writeln('Clearing existing users...');
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0;');
        foreach ([Admin::class, Agriculteur::class, Banque::class, Utilisateur::class] as $entityClass) {
            $tableName = $this->entityManager->getClassMetadata($entityClass)->getTableName();
            $connection->executeStatement($platform->getTruncateTableSQL($tableName, true));
        }
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1;');

        $adminUser = new Utilisateur();
        $adminUser->setEmail('admin@agrifund.com');
        $adminUser->setPrenom('Admin');
        $adminUser->setNom('User');
        $adminUser->setPassword($this->passwordHasher->hashPassword($adminUser, 'admin123'));
        $adminUser->setIsVerified(true);

        $admin = new Admin();
        $admin->setUtilisateur($adminUser);
        $adminUser->setAdmin($admin);

        $this->entityManager->persist($adminUser);
        $this->entityManager->persist($admin);

        $agriculteurUser = new Utilisateur();
        $agriculteurUser->setEmail('agri@agrifund.com');
        $agriculteurUser->setPrenom('Agri');
        $agriculteurUser->setNom('Culteur');
        $agriculteurUser->setPassword($this->passwordHasher->hashPassword($agriculteurUser, 'agri123'));
        $agriculteurUser->setIsVerified(true);

        $agriculteur = new Agriculteur();
        $agriculteur->setUtilisateur($agriculteurUser);
        $agriculteur->setStatuscompte('actif');
        $agriculteur->setCompteverifie(true);
        $agriculteurUser->setAgriculteur($agriculteur);

        $this->entityManager->persist($agriculteurUser);
        $this->entityManager->persist($agriculteur);

        $banqueUser = new Utilisateur();
        $banqueUser->setEmail('banque@agrifund.com');
        $banqueUser->setPrenom('Banque');
        $banqueUser->setNom('Agri');
        $banqueUser->setPassword($this->passwordHasher->hashPassword($banqueUser, 'banque123'));
        $banqueUser->setIsVerified(true);

        $banque = new Banque();
        $banque->setUtilisateur($banqueUser);
        $banque->setCodebanque('AGRIFUND-BANK');
        $banque->setStatusCompte('actif');
        $banque->setCompteVerfiee(true);
        $banqueUser->setBanque($banque);

        $this->entityManager->persist($banqueUser);
        $this->entityManager->persist($banque);

        $this->entityManager->flush();

        $io->success('Users created successfully.');
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
            'Login Page: http://localhost:8000/login',
            'Admin Panel: http://localhost:8000/admin/produits/',
            'Products: http://localhost:8000/admin/produits/',
            'Offers: http://localhost:8000/admin/offres/',
        ]);

        return Command::SUCCESS;
    }
}
