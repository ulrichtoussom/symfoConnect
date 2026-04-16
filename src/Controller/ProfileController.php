<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/profil/{username}', name: 'app_profile')]
    public function show(string $username, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy([
            'username' => $username
        ]);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        return $this->render('profile/show.html.twig', [
            'user' => $user,
            'username' => 'testuser', // à remplacer par app.user.username quand l'authentification sera en place
        ]);
    }
}