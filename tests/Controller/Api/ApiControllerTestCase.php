<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Entity\Admin;
use App\Entity\OffreFinanciere;
use App\Entity\ProduitFinancier;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class ApiControllerTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $this->resetDatabase();
    }

    private function resetDatabase(): void
    {
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        if (!$metadata) {
            return;
        }

        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    protected function createProduit(string $nom = 'Produit Test'): ProduitFinancier
    {
        $produit = new ProduitFinancier();
        $produit->setNomProduit($nom);
        $produit->setTypeFinancement('Crédit');
        $produit->setTauxInteret(5.5);
        $produit->setMontant(50000);
        $produit->setReglesFinancieres('Regles test');

        $this->entityManager->persist($produit);
        $this->entityManager->flush();

        return $produit;
    }

    protected function createOffre(ProduitFinancier $produit, string $nom = 'Offre Test'): OffreFinanciere
    {
        $offre = new OffreFinanciere();
        $offre->setNomOffre($nom);
        $offre->setConditions('Conditions test');
        $offre->setStatut('Active');
        $offre->setProduitFinancier($produit);

        $this->entityManager->persist($offre);
        $this->entityManager->flush();

        return $offre;
    }

    protected function loginAsAdmin(): void
    {
        $user = new Utilisateur();
        $user->setNom('Admin');
        $user->setPrenom('Test');
        $user->setEmail('admin+' . uniqid() . '@test.local');
        $user->setPassword('password');

        $admin = new Admin();
        $admin->setUtilisateur($user);
        $user->setAdmin($admin);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->loginUser($user, 'main');
    }
}
