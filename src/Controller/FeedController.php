<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FeedController extends AbstractController
{
    #[Route('/feed', name: 'app_feed')]
    public function index(PostRepository $postRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $user */
        $user = $this->getUser();

        $posts = $user->getFollowing()->isEmpty()
            ? []
            : $postRepository->findFeedForUser($user);

        return $this->render('feed/index.html.twig', [
            'posts' => $posts,
        ]);
    }
}
