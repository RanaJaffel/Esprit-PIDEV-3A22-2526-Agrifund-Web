<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Utilisateur>
 */
class UtilisateurRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
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

    /**
     * Trouve un utilisateur par email
     */
    public function findOneByEmail(string $email): ?Utilisateur
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Recherche d'utilisateurs (pour admin)
     */
    public function searchUtilisateurs(?string $search = null, ?string $type = null)
    {
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.admin', 'a')
            ->leftJoin('u.agriculteur', 'ag')
            ->leftJoin('u.banque', 'b')
            ->orderBy('u.dateInscrit', 'DESC');

        if ($search) {
            $qb->andWhere('u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($type) {
            switch ($type) {
                case 'admin':
                    $qb->andWhere('a.id IS NOT NULL');
                    break;
                case 'agriculteur':
                    $qb->andWhere('ag.id IS NOT NULL');
                    break;
                case 'banque':
                    $qb->andWhere('b.id IS NOT NULL');
                    break;
            }
        }

        return $qb->getQuery()->getResult();
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
            ->innerJoin('u.admin', 'a')
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve les utilisateurs pour la messagerie (admins et banques pour agriculteur)
     */
    public function findAvailableForMessaging(Utilisateur $currentUser): array
{
    $qb = $this->createQueryBuilder('u')
        ->leftJoin('u.admin', 'a')
        ->leftJoin('u.agriculteur', 'ag')
        ->leftJoin('u.banque', 'b')
        ->where('u.id != :currentId')
        ->setParameter('currentId', $currentUser->getId());

    // Admin peut parler à tout le monde
    if ($currentUser->getAdmin()) {
        // aucune restriction
    }
    // Banque peut parler aux admins et agriculteurs
    elseif ($currentUser->getBanque()) {
        $qb->andWhere('a.id IS NOT NULL OR ag.id IS NOT NULL');
    }
    // Agriculteur peut parler aux admins et banques
    elseif ($currentUser->getAgriculteur()) {
        $qb->andWhere('a.id IS NOT NULL OR b.id IS NOT NULL');
    }

    return $qb->orderBy('u.nom', 'ASC')
        ->getQuery()
        ->getResult();
}
}
