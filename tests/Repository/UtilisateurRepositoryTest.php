<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Admin;
use App\Entity\Agriculteur;
use App\Entity\Banque;
use App\Entity\Parametres2fa;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use App\Tests\Support\UserRoleSchemaTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class UtilisateurRepositoryTest extends KernelTestCase
{
    use UserRoleSchemaTrait;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->resetUserRoleSchema($this->entityManager);
    }

    public function testAdminFilterUsesInnerJoin(): void
    {
        $repository = $this->getRepository();

        $sql = $repository->createAdminUsersQueryBuilder(null, 'admin')
            ->getQuery()
            ->getSQL();

        self::assertStringContainsString('INNER JOIN admin', $sql);
        self::assertStringNotContainsString('LEFT JOIN admin', $sql);
    }

    public function testUtilisateurDoesNotMapInverseAdminAssociation(): void
    {
        $metadata = $this->entityManager->getClassMetadata(Utilisateur::class);

        self::assertArrayNotHasKey('admin', $metadata->associationMappings);
    }

    public function testUnfilteredAdminUsersQueryDoesNotJoinRoleTables(): void
    {
        $repository = $this->getRepository();

        $sql = $repository->createAdminUsersQueryBuilder()
            ->getQuery()
            ->getSQL();

        self::assertStringNotContainsString('JOIN admin', $sql);
        self::assertStringNotContainsString('JOIN agriculteur', $sql);
        self::assertStringNotContainsString('JOIN banque', $sql);
    }

    public function testRoleMapReturnsRolesForProvidedUsers(): void
    {
        $repository = $this->getRepository();

        $admin = $this->createUser('admin-role-map@example.test', 'Admin', 'Role', 'admin');
        $agriculteur = $this->createUser('agri-role-map@example.test', 'Agri', 'Role', 'agriculteur');
        $banque = $this->createUser('bank-role-map@example.test', 'Bank', 'Role', 'banque');
        $plain = $this->createUser('plain-role-map@example.test', 'Plain', 'Role', null);
        $this->entityManager->flush();

        $adminId = $admin->getId();
        $agriculteurId = $agriculteur->getId();
        $banqueId = $banque->getId();
        $plainId = $plain->getId();
        self::assertIsInt($adminId);
        self::assertIsInt($agriculteurId);
        self::assertIsInt($banqueId);
        self::assertIsInt($plainId);

        $roleByUserId = $repository->getRoleByUserIdForUsers([$admin, $agriculteur, $banque, $plain]);

        self::assertSame('admin', $roleByUserId[$adminId]);
        self::assertSame('agriculteur', $roleByUserId[$agriculteurId]);
        self::assertSame('banque', $roleByUserId[$banqueId]);
        self::assertArrayNotHasKey($plainId, $roleByUserId);
    }

    public function testSecurityRolesAreStoredOnUtilisateur(): void
    {
        $repository = $this->getRepository();
        $this->createUser('stored-admin-role@example.test', 'Stored', 'Admin', 'admin');
        $this->entityManager->flush();
        $this->entityManager->clear();

        $user = $repository->findOneByEmail('stored-admin-role@example.test');

        self::assertInstanceOf(Utilisateur::class, $user);
        self::assertSame(['ROLE_ADMIN', 'ROLE_USER'], $user->getRoles());
        self::assertSame('Admin', $user->getTypeUtilisateur());
    }

    public function testSecurityUserQueryDoesNotFetchJoinProfiles(): void
    {
        $repository = $this->getRepository();

        $sql = $this->createSecurityUserQueryBuilder($repository)
            ->andWhere('u.email = :email')
            ->setParameter('email', 'missing@example.test')
            ->getQuery()
            ->getSQL();

        self::assertStringNotContainsString('JOIN agriculteur', $sql);
        self::assertStringNotContainsString('JOIN banque', $sql);
        self::assertStringNotContainsString('JOIN parametres2fa', $sql);
    }

    public function testFindOneByEmailHydratesActiveTwoFactorSettings(): void
    {
        $repository = $this->getRepository();
        $user = $this->createUser('active-2fa@example.test', 'Active', 'TwoFactor', null);
        $parametres = (new Parametres2fa())
            ->setUtilisateur($user)
            ->setEstActive(true);
        $user->setParametres2fa($parametres);
        $this->entityManager->persist($parametres);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $loadedUser = $repository->findOneByEmail('active-2fa@example.test');

        self::assertInstanceOf(Utilisateur::class, $loadedUser);
        self::assertInstanceOf(Parametres2fa::class, $loadedUser->getParametres2fa());
        self::assertTrue($loadedUser->has2FAEnabled());
    }

    public function testRemovingRoleProfileUpdatesStoredRoles(): void
    {
        $user = $this->createUser('remove-admin-role@example.test', 'Remove', 'Admin', 'admin');

        self::assertContains('ROLE_ADMIN', $user->getRoles());

        $user->setAdmin(null);

        self::assertNotContains('ROLE_ADMIN', $user->getRoles());
        self::assertSame(['ROLE_USER'], $user->getRoles());
    }

    public function testMessagingQueryForBankUsesExistsInsteadOfRoleLeftJoins(): void
    {
        $repository = $this->getRepository();
        $currentBank = $this->createUser('current-bank@example.test', 'Current', 'Bank', 'banque');
        $this->entityManager->flush();

        $sql = $repository->createAvailableForMessagingQueryBuilder($currentBank)
            ->getQuery()
            ->getSQL();

        self::assertStringContainsString('EXISTS', $sql);
        self::assertStringContainsString('admin', $sql);
        self::assertStringNotContainsString('LEFT JOIN admin', $sql);
        self::assertStringNotContainsString('LEFT JOIN agriculteur', $sql);
        self::assertStringNotContainsString('LEFT JOIN banque', $sql);
    }

    public function testMessagingRecipientsForBankKeepExpectedRoleRules(): void
    {
        $repository = $this->getRepository();
        $currentBank = $this->createUser('current-bank-rules@example.test', 'Current', 'Bank', 'banque');
        $admin = $this->createUser('available-admin@example.test', 'Available', 'Admin', 'admin');
        $agriculteur = $this->createUser('available-agri@example.test', 'Available', 'Agri', 'agriculteur');
        $otherBank = $this->createUser('other-bank@example.test', 'Other', 'Bank', 'banque');
        $plain = $this->createUser('available-plain@example.test', 'Available', 'Plain', null);
        $this->entityManager->flush();

        $emails = array_map(
            static fn (Utilisateur $user): ?string => $user->getEmail(),
            $repository->findAvailableForMessaging($currentBank)
        );

        self::assertContains($admin->getEmail(), $emails);
        self::assertContains($agriculteur->getEmail(), $emails);
        self::assertNotContains($currentBank->getEmail(), $emails);
        self::assertNotContains($otherBank->getEmail(), $emails);
        self::assertNotContains($plain->getEmail(), $emails);
    }

    private function getRepository(): UtilisateurRepository
    {
        $repository = $this->entityManager->getRepository(Utilisateur::class);
        self::assertInstanceOf(UtilisateurRepository::class, $repository);

        return $repository;
    }

    private function createSecurityUserQueryBuilder(UtilisateurRepository $repository): QueryBuilder
    {
        $reflectionMethod = new \ReflectionMethod($repository, 'createSecurityUserQueryBuilder');
        $reflectionMethod->setAccessible(true);
        $queryBuilder = $reflectionMethod->invoke($repository);

        self::assertInstanceOf(QueryBuilder::class, $queryBuilder);

        return $queryBuilder;
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
