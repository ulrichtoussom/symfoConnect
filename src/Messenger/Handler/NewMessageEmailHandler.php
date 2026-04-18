<?php

namespace App\Messenger\Handler;

use App\Messenger\NewMessageEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
final class NewMessageEmailHandler
{
    public function __construct(private readonly MailerInterface $mailer) {}

    public function __invoke(NewMessageEmail $message): void
    {
        $email = (new Email())
            ->from('noreply@symfoconnect.local')
            ->to($message->recipientEmail)
            ->subject(sprintf('[SymfoConnect] Nouveau message de %s', $message->senderUsername))
            ->html(sprintf(
                '<p>Bonjour <strong>%s</strong>,</p>
                 <p><strong>%s</strong> vous a envoyé un message :</p>
                 <blockquote style="border-left:3px solid #6366f1;padding-left:12px;color:#555">%s</blockquote>
                 <p><a href="http://localhost:8000/messages/%s">Voir la conversation →</a></p>',
                htmlspecialchars($message->recipientUsername),
                htmlspecialchars($message->senderUsername),
                htmlspecialchars($message->messagePreview),
                htmlspecialchars($message->senderUsername),
            ));

        $this->mailer->send($email);
    }
}
