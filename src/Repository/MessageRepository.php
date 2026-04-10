<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * Trouve les messages d'une conversation
     */
    public function findByConversation(Conversation $conversation, bool $includeDeleted = false): array
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->orderBy('m.dateEnvoi', 'ASC');

        if (!$includeDeleted) {
            $qb->andWhere('m.estSupprime = :deleted')
                ->setParameter('deleted', false);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Marque les messages comme lus
     */
    public function markAsRead(Conversation $conversation, int $userId): void
    {
        $this->createQueryBuilder('m')
            ->update()
            ->set('m.estLu', ':read')
            ->set('m.dateLecture', ':now')
            ->where('m.conversation = :conversation')
            ->andWhere('m.expediteur != :userId')
            ->andWhere('m.estLu = :notRead')
            ->setParameter('read', true)
            ->setParameter('now', new \DateTime())
            ->setParameter('conversation', $conversation)
            ->setParameter('userId', $userId)
            ->setParameter('notRead', false)
            ->getQuery()
            ->execute();

        $this->getEntityManager()->flush();
    }

    /**
     * Compte les messages non lus pour un utilisateur dans une conversation
     */
    public function countUnreadMessages(Conversation $conversation, int $userId): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.conversation = :conversation')
            ->andWhere('m.expediteur != :userId')
            ->andWhere('m.estLu = :read')
            ->andWhere('m.estSupprime = :deleted')
            ->setParameter('conversation', $conversation)
            ->setParameter('userId', $userId)
            ->setParameter('read', false)
            ->setParameter('deleted', false)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte tous les messages non lus pour un utilisateur
     */
    public function countAllUnreadMessages(int $userId): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->join('m.conversation', 'c')
            ->where('(c.utilisateur1Id = :userId OR c.utilisateur2Id = :userId)')
            ->andWhere('m.expediteur != :userId')
            ->andWhere('m.estLu = :read')
            ->andWhere('m.estSupprime = :deleted')
            ->setParameter('userId', $userId)
            ->setParameter('read', false)
            ->setParameter('deleted', false)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Supprime (soft delete) un message
     */
    public function softDelete(Message $message): void
    {
        $message->setEstSupprime(true);
        $this->getEntityManager()->flush();
    }

    /**
     * Recherche dans les messages
     */
    public function searchMessages(Conversation $conversation, string $query): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.conversation = :conversation')
            ->andWhere('m.contenu LIKE :query')
            ->andWhere('m.estSupprime = :deleted')
            ->setParameter('conversation', $conversation)
            ->setParameter('query', '%' . $query . '%')
            ->setParameter('deleted', false)
            ->orderBy('m.dateEnvoi', 'DESC')
            ->getQuery()
            ->getResult();
    }
}