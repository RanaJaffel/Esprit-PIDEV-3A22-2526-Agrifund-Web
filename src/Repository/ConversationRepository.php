<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conversation>
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    /**
     * Trouve ou crée une conversation entre deux utilisateurs
     */
    public function findOrCreateConversation(int $user1Id, int $user2Id): Conversation
    {
        $conversation = $this->createQueryBuilder('c')
            ->where('(c.utilisateur1Id = :user1 AND c.utilisateur2Id = :user2) OR (c.utilisateur1Id = :user2 AND c.utilisateur2Id = :user1)')
            ->setParameter('user1', $user1Id)
            ->setParameter('user2', $user2Id)
            ->getQuery()
            ->getOneOrNullResult();

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

    /**
     * Trouve les conversations d'un utilisateur
     */
    public function findUserConversations(int $userId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.utilisateur1Id = :userId OR c.utilisateur2Id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('c.derniereActivite', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve une conversation entre deux utilisateurs
     */
    public function findConversationBetweenUsers(int $user1Id, int $user2Id): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->where('(c.utilisateur1Id = :user1 AND c.utilisateur2Id = :user2) OR (c.utilisateur1Id = :user2 AND c.utilisateur2Id = :user1)')
            ->setParameter('user1', $user1Id)
            ->setParameter('user2', $user2Id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Compte les conversations non lues pour un utilisateur
     */
    public function countUnreadConversations(int $userId): int
    {
        $conversations = $this->findUserConversations($userId);
        $count = 0;

        foreach ($conversations as $conversation) {
            if ($conversation->hasUnreadMessages($userId)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Met à jour la dernière activité d'une conversation
     */
    public function updateLastActivity(Conversation $conversation): void
    {
        $conversation->setDerniereActivite(new \DateTime());
        $this->getEntityManager()->flush();
    }
}