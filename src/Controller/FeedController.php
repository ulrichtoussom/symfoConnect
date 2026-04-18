<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class FeedController extends AbstractController
{
    public function __construct(private readonly TagAwareCacheInterface $cache) {}

    #[Route('/feed', name: 'app_feed')]
    public function index(PostRepository $postRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $user */
        $user = $this->getUser();

        if ($user->getFollowing()->isEmpty()) {
            return $this->render('feed/index.html.twig', ['posts' => []]);
        }

        $cacheKey = 'feed_user_' . $user->getId();

        $posts = $this->cache->get($cacheKey, function (ItemInterface $item) use ($user, $postRepository) {
            $item->expiresAfter(300); // 5 minutes
            $item->tag(['feed', 'feed_user_' . $user->getId()]);

            return $postRepository->findFeedForUser($user);
        });

        return $this->render('feed/index.html.twig', [
            'posts' => $posts,
        ]);
    }
}
