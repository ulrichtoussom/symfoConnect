<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    #[Route('/post/nouveau', name: 'app_post_new')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository
    ): Response {
        if ($request->isMethod('POST')) {
            $content = $request->request->get('content');

            if (!$content || strlen($content) < 5) {
                $this->addFlash('error', 'Le contenu doit faire au moins 5 caractères');
                return $this->redirectToRoute('app_post_new');
            }

            // ⚠️ utilisateur simulé (id=1)
            $user = $userRepository->find(1);
            /* $user = $userRepository->findOneBy([]);

            if (!$user) {
                throw new \Exception('Aucun utilisateur trouvé en base');
            } */
            $post = new Post();
            $post->setContent($content);
            $post->setCreatedAt(new \DateTimeImmutable());
            $post->setAuthor($user);

            $em->persist($post);
            $em->flush();

            $this->addFlash('success', 'Post créé avec succès');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('post/new.html.twig');
    }
}