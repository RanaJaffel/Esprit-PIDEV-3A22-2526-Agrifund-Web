<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Admin;
use App\Entity\Agriculteur;
use App\Entity\Banque;
use App\Entity\Utilisateur;
use App\Tests\Support\UserRoleSchemaTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

final class AdminUserControllerTest extends WebTestCase
{
    use UserRoleSchemaTrait;

    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->resetUserRoleSchema($this->entityManager);
    }

    public function testAdminUsersIndexRendersUsersAndRoleBadges(): void
    {
        $this->loginAsAdmin();
        $this->createUser('admin-list@example.test', 'Admin', 'List', 'admin');
        $this->createUser('agri-list@example.test', 'Agri', 'List', 'agriculteur');
        $this->createUser('bank-list@example.test', 'Bank', 'List', 'banque');
        $this->createUser('plain-list@example.test', 'Plain', 'List', null);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/admin/utilisateurs/');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Admin', $this->rowTextForEmail($crawler, 'admin-list@example.test'));
        self::assertStringContainsString('Agriculteur', $this->rowTextForEmail($crawler, 'agri-list@example.test'));
        self::assertStringContainsString('Banque', $this->rowTextForEmail($crawler, 'bank-list@example.test'));

        $plainRowText = $this->rowTextForEmail($crawler, 'plain-list@example.test');
        self::assertStringNotContainsString('Admin', $plainRowText);
        self::assertStringNotContainsString('Agriculteur', $plainRowText);
        self::assertStringNotContainsString('Banque', $plainRowText);
    }

    public function testAdminUsersIndexAdminFilterReturnsOnlyAdmins(): void
    {
        $this->loginAsAdmin();
        $this->createUser('filtered-admin@example.test', 'Filtered', 'Admin', 'admin');
        $this->createUser('filtered-agri@example.test', 'Filtered', 'Agri', 'agriculteur');
        $this->createUser('filtered-bank@example.test', 'Filtered', 'Bank', 'banque');
        $this->createUser('filtered-plain@example.test', 'Filtered', 'Plain', null);
        $this->entityManager->flush();

        $this->client->request('GET', '/admin/utilisateurs/?type=admin');
        $html = (string) $this->client->getResponse()->getContent();

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('filtered-admin@example.test', $html);
        self::assertStringNotContainsString('filtered-agri@example.test', $html);
        self::assertStringNotContainsString('filtered-bank@example.test', $html);
        self::assertStringNotContainsString('filtered-plain@example.test', $html);
    }

    private function rowTextForEmail(Crawler $crawler, string $email): string
    {
        $rows = $crawler->filter('tbody tr')->reduce(
            static fn (Crawler $row): bool => str_contains($row->text(), $email)
        );

        self::assertCount(1, $rows, sprintf('Expected one table row for "%s".', $email));

        return $rows->first()->text();
    }

    private function loginAsAdmin(): void
    {
        $user = $this->createUser(
            'current-admin-' . uniqid() . '@example.test',
            'Current',
            'Admin',
            'admin'
        );
        $this->entityManager->flush();

        $this->client->loginUser($user, 'main');
    }

    private function createUser(string $email, string $prenom, string $nom, ?string $role): Utilisateur
    {
        $user = (new Utilisateur())
            ->setPrenom($prenom)
            ->setNom($nom)
            ->setEmail($email)
            ->setPassword('password');

        $this->entityManager->persist($user);

        if ($role === 'admin') {
            $admin = (new Admin())->setUtilisateur($user);
            $user->setAdmin($admin);
            $this->entityManager->persist($admin);
        }

        if ($role === 'agriculteur') {
            $agriculteur = (new Agriculteur())->setUtilisateur($user);
            $user->setAgriculteur($agriculteur);
            $this->entityManager->persist($agriculteur);
        }

        if ($role === 'banque') {
            $banque = (new Banque())
                ->setUtilisateur($user)
                ->setCodebanque('BANK-' . substr(md5($email), 0, 8));
            $user->setBanque($banque);
            $this->entityManager->persist($banque);
        }

        return $user;
    }
}
