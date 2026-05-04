<?php

namespace App\Repository;

use App\Entity\Admin;
use App\Entity\Agriculteur;
use App\Entity\Banque;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @extends ServiceEntityRepository<Utilisateur>
 * @implements UserProviderInterface<Utilisateur>
 */
class UtilisateurRepository extends ServiceEntityRepository implements PasswordUpgraderInterface, UserLoaderInterface, UserProviderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Utilisateur) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->findOneByEmail($identifier);

        if (!$user instanceof Utilisateur) {
            $exception = new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
            $exception->setUserIdentifier($identifier);

            throw $exception;
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof Utilisateur) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_debug_type($user)));
        }

        $id = $user->getId();
        if ($id === null) {
            $exception = new UserNotFoundException('Cannot refresh a user without an id.');
            $exception->setUserIdentifier($user->getUserIdentifier());

            throw $exception;
        }

        $refreshedUser = $this->createSecurityUserQueryBuilder()
            ->andWhere('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$refreshedUser instanceof Utilisateur) {
            $exception = new UserNotFoundException(sprintf('User "%s" not found.', $user->getUserIdentifier()));
            $exception->setUserIdentifier($user->getUserIdentifier());

            throw $exception;
        }

        $this->hydrateSecurityUserRelations($refreshedUser);

        return $refreshedUser;
    }

    public function supportsClass(string $class): bool
    {
        return $class === Utilisateur::class || is_subclass_of($class, Utilisateur::class);
    }

    /**
     * Trouve un utilisateur par email
     */
    public function findOneByEmail(string $email): ?Utilisateur
    {
        $user = $this->createSecurityUserQueryBuilder()
            ->andWhere('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();

        if ($user instanceof Utilisateur) {
            $this->hydrateSecurityUserRelations($user);
        }

        return $user instanceof Utilisateur ? $user : null;
    }

    private function createSecurityUserQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->select('u');
    }

    private function hydrateSecurityUserRelations(Utilisateur $user): void
    {
        $id = $user->getId();
        if ($id === null) {
            return;
        }

        $entityManager = $this->getEntityManager();

        $associations = [
            'parametres2fa' => 'parametres2fa',
        ];

        if ($user->hasRole('ROLE_AGRICULTEUR')) {
            $associations['agriculteur'] = 'agriculteurProfile';
        }
        if ($user->hasRole('ROLE_BANQUE')) {
            $associations['banque'] = 'banqueProfile';
        }

        foreach ($associations as $association => $alias) {
            $entityManager->createQuery(sprintf(
                'SELECT PARTIAL u.{id}, %s FROM %s u LEFT JOIN u.%s %s WHERE u.id = :id',
                $alias,
                Utilisateur::class,
                $association,
                $alias
            ))
                ->setParameter('id', $id)
                ->getResult();
        }
    }

    /**
     * Recherche d'utilisateurs (pour admin)
     *
     * @return list<Utilisateur>
     */
    public function searchUtilisateurs(?string $search = null, ?string $type = null): array
    {
        return $this->createAdminUsersQueryBuilder($search, $type)
            ->getQuery()
            ->getResult();
    }

    /**
     * Query builder pour la liste admin des utilisateurs.
     */
    public function createAdminUsersQueryBuilder(?string $search = null, ?string $type = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u')
            ->orderBy('u.dateInscrit', 'DESC');

        if ($search) {
            $qb->andWhere('u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        switch ($type) {
            case 'admin':
                $qb->innerJoin(Admin::class, 'a', 'WITH', 'a.utilisateur = u');
                break;
            case 'agriculteur':
                $qb->innerJoin(Agriculteur::class, 'ag', 'WITH', 'ag.utilisateur = u');
                break;
            case 'banque':
                $qb->innerJoin(Banque::class, 'b', 'WITH', 'b.utilisateur = u');
                break;
        }

        return $qb;
    }

    /**
     * @param iterable<Utilisateur> $utilisateurs
     *
     * @return array<int, 'admin'|'agriculteur'|'banque'>
     */
    public function getRoleByUserIdForUsers(iterable $utilisateurs): array
    {
        $userIds = [];

        foreach ($utilisateurs as $utilisateur) {
            $id = $utilisateur->getId();
            if ($id !== null) {
                $userIds[] = $id;
            }
        }

        $userIds = array_values(array_unique($userIds));
        if ($userIds === []) {
            return [];
        }

        $roleByUserId = [];
        foreach ($this->findRoleUserIds(Admin::class, 'adminRole', $userIds) as $userId) {
            $roleByUserId[$userId] = 'admin';
        }
        foreach ($this->findRoleUserIds(Agriculteur::class, 'agriculteurRole', $userIds) as $userId) {
            $roleByUserId[$userId] ??= 'agriculteur';
        }
        foreach ($this->findRoleUserIds(Banque::class, 'banqueRole', $userIds) as $userId) {
            $roleByUserId[$userId] ??= 'banque';
        }

        return $roleByUserId;
    }

    /**
     * @param list<int> $userIds
     *
     * @return list<int>
     */
    private function findRoleUserIds(string $entityClass, string $alias, array $userIds): array
    {
        $rows = $this->getEntityManager()
            ->createQueryBuilder()
            ->select(sprintf('IDENTITY(%s.utilisateur)', $alias))
            ->from($entityClass, $alias)
            ->where(sprintf('%s.utilisateur IN (:userIds)', $alias))
            ->setParameter('userIds', $userIds)
            ->getQuery()
            ->getSingleColumnResult();

        return array_map('intval', $rows);
    }

    /**
     * Compte les utilisateurs par type
     */
    public function countByType(): array
    {
        $em = $this->getEntityManager();
        
        return [
            'total' => (int) $this->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->getQuery()
                ->getSingleScalarResult(),
            'admins' => (int) $em->createQuery('SELECT COUNT(a.id) FROM App\Entity\Admin a')
                ->getSingleScalarResult(),
            'agriculteurs' => (int) $em->createQuery('SELECT COUNT(ag.id) FROM App\Entity\Agriculteur ag')
                ->getSingleScalarResult(),
            'banques' => (int) $em->createQuery('SELECT COUNT(b.id) FROM App\Entity\Banque b')
                ->getSingleScalarResult(),
        ];
    }

    /**
     * Utilisateurs récemment inscrits
     */
    public function findRecentUsers(int $limit = 5): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.dateInscrit', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Utilisateurs en ligne
     */
    public function findOnlineUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.estEnLigne = :online')
            ->setParameter('online', true)
            ->getQuery()
            ->getResult();
    }

    public function findFirstAdmin(): ?Utilisateur
    {
        return $this->createQueryBuilder('u')
            ->innerJoin(Admin::class, 'a', 'WITH', 'a.utilisateur = u')
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve les utilisateurs pour la messagerie (admins et banques pour agriculteur)
     *
     * @return list<Utilisateur>
     */
    public function findAvailableForMessaging(Utilisateur $currentUser): array
    {
        return $this->createAvailableForMessagingQueryBuilder($currentUser)
            ->getQuery()
            ->getResult();
    }

    public function createAvailableForMessagingQueryBuilder(Utilisateur $currentUser): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.id != :currentId')
            ->setParameter('currentId', $currentUser->getId());

        // Admin peut parler a tout le monde.
        if ($currentUser->hasRole('ROLE_ADMIN')) {
            // Aucune restriction.
        } elseif ($currentUser->hasRole('ROLE_BANQUE')) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $this->roleExistsExpression(Admin::class, 'availableAdmin'),
                    $this->roleExistsExpression(Agriculteur::class, 'availableAgriculteur')
                )
            );
        } elseif ($currentUser->hasRole('ROLE_AGRICULTEUR')) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $this->roleExistsExpression(Admin::class, 'availableAdmin'),
                    $this->roleExistsExpression(Banque::class, 'availableBanque')
                )
            );
        }

        return $qb->orderBy('u.nom', 'ASC');
    }

    private function roleExistsExpression(string $entityClass, string $alias): string
    {
        return sprintf(
            'EXISTS (SELECT %s.id FROM %s %s WHERE %s.utilisateur = u)',
            $alias,
            $entityClass,
            $alias,
            $alias
        );
    }
}
