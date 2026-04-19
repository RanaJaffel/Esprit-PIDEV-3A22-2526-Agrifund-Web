<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    public function findOrCreateConversation(int $user1Id, int $user2Id): Conversation
    {
        $conversation = $this->findConversationBetweenUsers($user1Id, $user2Id);

        if (!$conversation) {
            $conversation = new Conversation();
            $conversation->setUtilisateur1Id($user1Id);
            $conversation->setUtilisateur2Id($user2Id);
            
            $em = $this->getEntityManager();
            $em->persist($conversation);
            $em->flush();
        }

        return $conversation;
    }

    public function findUserConversations(int $userId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.utilisateur1Id = :userId OR c.utilisateur2Id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('c.derniereActivite', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findConversationBetweenUsers(int $user1Id, int $user2Id): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->where('(c.utilisateur1Id = :user1 AND c.utilisateur2Id = :user2) OR (c.utilisateur1Id = :user2 AND c.utilisateur2Id = :user1)')
            ->setParameter('user1', $user1Id)
            ->setParameter('user2', $user2Id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countUnreadConversations(int $userId): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(DISTINCT c.id)')
            ->innerJoin('c.messages', 'm')
            ->where('c.utilisateur1Id = :userId OR c.utilisateur2Id = :userId')
            ->andWhere('IDENTITY(m.expediteur) != :userId')
            ->andWhere('m.estLu = :isRead')
            ->andWhere('m.estSupprime = :isDeleted')
            ->setParameter('userId', $userId)
            ->setParameter('isRead', false)
            ->setParameter('isDeleted', false)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function updateLastActivity(Conversation $conversation): void
    {
        $conversation->setDerniereActivite(new \DateTime());
        $this->getEntityManager()->flush();
    }

    /**
     * Récupère l'utilisateur complet par son ID
     */
    public function getUserById(int $userId): ?Utilisateur
    {
        return $this->getEntityManager()
            ->getRepository(Utilisateur::class)
            ->find($userId);
    }
}