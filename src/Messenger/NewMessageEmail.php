<?php

namespace App\Messenger;

/**
 * Message Messenger dispatché lors de l'envoi d'un message privé.
 * Transporté de manière asynchrone via le transport doctrine.
 */
final class NewMessageEmail
{
    public function __construct(
        public readonly string $recipientEmail,
        public readonly string $recipientUsername,
        public readonly string $senderUsername,
        public readonly string $messagePreview,
    ) {}
}
