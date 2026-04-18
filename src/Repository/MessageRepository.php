<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Message> */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * Retourne la liste des conversations d'un utilisateur
     * (un tableau associatif par interlocuteur, avec le dernier message).
     */
    public function findConversations(User $user): array
    {
        $messages = $this->createQueryBuilder('m')
            ->join('m.sender', 's')->addSelect('s')
            ->join('m.recipient', 'r')->addSelect('r')
            ->where('m.sender = :user OR m.recipient = :user')
            ->setParameter('user', $user)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $conversations = [];

        foreach ($messages as $message) {
            $partner = $message->getSender() === $user
                ? $message->getRecipient()
                : $message->getSender();

            $pid = $partner->getId();

            if (!isset($conversations[$pid])) {
                $conversations[$pid] = [
                    'partner'     => $partner,
                    'lastMessage' => $message,
                    'unreadCount' => 0,
                ];
            }

            if (!$message->isRead() && $message->getRecipient() === $user) {
                $conversations[$pid]['unreadCount']++;
            }
        }

        return array_values($conversations);
    }

    /**
     * Retourne les messages échangés entre deux utilisateurs (ordre chronologique).
     *
     * @return Message[]
     */
    public function findConversation(User $userA, User $userB): array
    {
        return $this->createQueryBuilder('m')
            ->join('m.sender', 's')->addSelect('s')
            ->join('m.recipient', 'r')->addSelect('r')
            ->where('(m.sender = :a AND m.recipient = :b) OR (m.sender = :b AND m.recipient = :a)')
            ->setParameter('a', $userA)
            ->setParameter('b', $userB)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Marque comme lus tous les messages reçus par $reader dans sa conversation avec $sender.
     */
    public function markAsRead(User $reader, User $sender): void
    {
        $this->createQueryBuilder('m')
            ->update()
            ->set('m.isRead', 'true')
            ->where('m.recipient = :reader AND m.sender = :sender AND m.isRead = false')
            ->setParameter('reader', $reader)
            ->setParameter('sender', $sender)
            ->getQuery()
            ->execute();
    }
}
