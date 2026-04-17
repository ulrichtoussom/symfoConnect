<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Messenger\NewMessageEmail;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/messages', name: 'app_messages')]
class MessageController extends AbstractController
{
    /**
     * Liste de toutes les conversations.
     */
    #[Route('', name: '_index')]
    public function index(MessageRepository $messageRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $user */
        $user = $this->getUser();

        $conversations = $messageRepository->findConversations($user);

        return $this->render('message/index.html.twig', [
            'conversations' => $conversations,
        ]);
    }

    /**
     * Conversation avec un utilisateur donné (affichage + envoi).
     */
    #[Route('/{username}', name: '_show')]
    public function show(
        string $username,
        Request $request,
        UserRepository $userRepository,
        MessageRepository $messageRepository,
        EntityManagerInterface $em,
        MessageBusInterface $bus,
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $partner = $userRepository->findOneBy(['username' => $username]);

        if (!$partner) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        if ($partner === $currentUser) {
            $this->addFlash('error', 'Tu ne peux pas t\'envoyer un message à toi-même.');
            return $this->redirectToRoute('app_messages_index');
        }

        // Marquer les messages reçus comme lus
        $messageRepository->markAsRead($currentUser, $partner);

        // Envoi d'un nouveau message
        if ($request->isMethod('POST')) {
            $content = trim((string) $request->request->get('content', ''));

            if (strlen($content) < 1) {
                $this->addFlash('error', 'Le message ne peut pas être vide.');
            } else {
                $message = (new Message())
                    ->setSender($currentUser)
                    ->setRecipient($partner)
                    ->setContent($content);

                $em->persist($message);
                $em->flush();

                // Email asynchrone via Messenger
                $bus->dispatch(new NewMessageEmail(
                    recipientEmail:    $partner->getEmail(),
                    recipientUsername: $partner->getUsername(),
                    senderUsername:    $currentUser->getUsername(),
                    messagePreview:    mb_substr($content, 0, 100),
                ));
            }

            return $this->redirectToRoute('app_messages_show', ['username' => $username]);
        }

        $messages = $messageRepository->findConversation($currentUser, $partner);

        return $this->render('message/show.html.twig', [
            'partner'  => $partner,
            'messages' => $messages,
        ]);
    }
}
