<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function findByConversation(Conversation $conversation, bool $includeDeleted = false): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.piecesJointes', 'pj')
            ->addSelect('pj')
            ->where('m.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->orderBy('m.dateEnvoi', 'ASC');

        if (!$includeDeleted) {
            $qb->andWhere('m.estSupprime = :deleted')
                ->setParameter('deleted', false);
        }

        return $qb->getQuery()->getResult();
    }

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
    }

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
     * @param int[] $conversationIds
     *
     * @return array<int, Message>
     */
    public function findLatestMessagesByConversations(array $conversationIds): array
    {
        if ($conversationIds === []) {
            return [];
        }

        $subQuery = $this->createQueryBuilder('m2')
            ->select('MAX(m2.id)')
            ->where('m2.conversation IN (:conversationIds)')
            ->andWhere('m2.estSupprime = :deleted')
            ->groupBy('m2.conversation');

        $messages = $this->createQueryBuilder('m')
            ->leftJoin('m.piecesJointes', 'pj')
            ->addSelect('pj')
            ->where('m.id IN (' . $subQuery->getDQL() . ')')
            ->setParameter('conversationIds', $conversationIds)
            ->setParameter('deleted', false)
            ->orderBy('m.dateEnvoi', 'DESC')
            ->getQuery()
            ->getResult();

        $byConversation = [];
        foreach ($messages as $message) {
            $conversationId = $message->getConversation()?->getId();
            if ($conversationId === null || isset($byConversation[$conversationId])) {
                continue;
            }

            $byConversation[$conversationId] = $message;
        }

        return $byConversation;
    }

    /**
     * @param int[] $conversationIds
     *
     * @return array<int, int>
     */
    public function countUnreadMessagesByConversations(array $conversationIds, int $userId): array
    {
        if ($conversationIds === []) {
            return [];
        }

        $rows = $this->createQueryBuilder('m')
            ->select('IDENTITY(m.conversation) AS conversationId, COUNT(m.id) AS unreadCount')
            ->where('m.conversation IN (:conversationIds)')
            ->andWhere('IDENTITY(m.expediteur) != :userId')
            ->andWhere('m.estLu = :read')
            ->andWhere('m.estSupprime = :deleted')
            ->setParameter('conversationIds', $conversationIds)
            ->setParameter('userId', $userId)
            ->setParameter('read', false)
            ->setParameter('deleted', false)
            ->groupBy('m.conversation')
            ->getQuery()
            ->getArrayResult();

        $counts = [];
        foreach ($rows as $row) {
            $conversationId = (int) ($row['conversationId'] ?? 0);
            if ($conversationId <= 0) {
                continue;
            }

            $counts[$conversationId] = (int) ($row['unreadCount'] ?? 0);
        }

        return $counts;
    }

    public function softDelete(Message $message): void
    {
        $message->setEstSupprime(true);
        $this->getEntityManager()->flush();
    }
}