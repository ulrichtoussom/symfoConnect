<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class PostController extends AbstractController
{
    public function __construct(private readonly TagAwareCacheInterface $cache) {}

    #[Route('/post/nouveau', name: 'app_post_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($request->isMethod('POST')) {
            $content = trim((string) $request->request->get('content', ''));

            if (strlen($content) < 5) {
                $this->addFlash('error', 'Le contenu doit faire au moins 5 caractères');
                return $this->redirectToRoute('app_post_new');
            }

            /** @var User $user */
            $user = $this->getUser();

            $post = (new Post())
                ->setContent($content)
                ->setAuthor($user);

            $em->persist($post);
            $em->flush();

            // Invalider le cache des feeds des abonnés
            $tags = ['feed'];
            foreach ($user->getFollowers() as $follower) {
                $tags[] = 'feed_user_' . $follower->getId();
            }
            $this->cache->invalidateTags($tags);

            $this->addFlash('success', 'Post créé avec succès !');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('post/new.html.twig');
    }
}
