<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FollowController extends AbstractController
{
    #[Route('/follow/{id}', name: 'app_follow')]
    public function follow(User $userToFollow, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if ($currentUser === $userToFollow) {
            $this->addFlash('error', 'Tu ne peux pas te suivre toi-même');
            return $this->redirectToRoute('app_profile', ['username' => $userToFollow->getUsername()]);
        }

        if (!$currentUser->getFollowing()->contains($userToFollow)) {
            $currentUser->addFollowing($userToFollow);

            $notification = (new Notification())
                ->setRecipient($userToFollow)
                ->setSender($currentUser)
                ->setType(Notification::TYPE_FOLLOW);

            $em->persist($notification);
            $em->flush();

            $this->addFlash('success', 'Utilisateur suivi');
        }

        return $this->redirectToRoute('app_profile', ['username' => $userToFollow->getUsername()]);
    }

    #[Route('/unfollow/{id}', name: 'app_unfollow')]
    public function unfollow(User $userToUnfollow, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if ($currentUser->getFollowing()->contains($userToUnfollow)) {
            $currentUser->removeFollowing($userToUnfollow);
            $em->flush();
            $this->addFlash('success', 'Abonnement annulé');
        }

        return $this->redirectToRoute('app_profile', ['username' => $userToUnfollow->getUsername()]);
    }
}
