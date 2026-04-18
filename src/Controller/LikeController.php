<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LikeController extends AbstractController
{
    #[Route('/like/{id}', name: 'app_like')]
    public function like(Post $post, EntityManagerInterface $em, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $user */
        $user = $this->getUser();

        if (!$post->getLikedBy()->contains($user)) {
            $post->addLikedBy($user);

            if ($post->getAuthor() !== $user) {
                $notification = (new Notification())
                    ->setRecipient($post->getAuthor())
                    ->setSender($user)
                    ->setType(Notification::TYPE_LIKE);
                $em->persist($notification);
            }

            $em->flush();
        }

        return $this->redirect($request->headers->get('referer', $this->generateUrl('app_home')));
    }

    #[Route('/unlike/{id}', name: 'app_unlike')]
    public function unlike(Post $post, EntityManagerInterface $em, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $user */
        $user = $this->getUser();

        if ($post->getLikedBy()->contains($user)) {
            $post->removeLikedBy($user);
            $em->flush();
        }

        return $this->redirect($request->headers->get('referer', $this->generateUrl('app_home')));
    }
}
