<?php

namespace App\DataFixtures;

use App\Entity\Notification;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        // ── Utilisateurs ──────────────────────────────────────────────
        $userData = [
            ['alice', 'alice@test.com', 'Développeuse passionnée de PHP et Symfony.'],
            ['bob',   'bob@test.com',   'Fan de café et de clean code.'],
            ['clara', 'clara@test.com', 'Designer UI/UX qui code un peu aussi.'],
            ['david', 'david@test.com', 'Étudiant en informatique. Curieux de tout.'],
            ['emma',  'emma@test.com',  'DevOps. "Ça marche en local." 🐳'],
        ];

        /** @var User[] $users */
        $users = [];
        foreach ($userData as [$username, $email, $bio]) {
            $user = new User();
            $user->setEmail($email);
            $user->setUsername($username);
            $user->setBio($bio);
            $user->setCreatedAt(new \DateTimeImmutable('-' . rand(10, 60) . ' days'));
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $manager->persist($user);
            $users[] = $user;
        }

        // ── Posts ──────────────────────────────────────────────────────
        $postContents = [
            'alice'  => [
                'Je viens de finir mon premier bundle Symfony custom, trop content !',
                'Pro tip : utilisez le Profiler pour déboguer vos requêtes Doctrine.',
                'Découverte du jour : les Twig Components. Vraiment sympa pour les composants réutilisables.',
            ],
            'bob'    => [
                'Troisième café de la journée. Les tests passent enfin. Coïncidence ? Je ne crois pas.',
                'Le code le plus propre est celui qu\'on n\'a pas à écrire.',
                'PR mergée après 3 rounds de review. Le moment de victoire de la semaine.',
            ],
            'clara'  => [
                'Nouveau design pour l\'app en cours. Tailwind CSS c\'est vraiment agréable à utiliser.',
                'Rappel : l\'accessibilité, c\'est pas optionnel. Pensez aux contrastes et aux attributs alt !',
                'Figma → Twig → Profit. Ma stack front en 2026.',
            ],
            'david'  => [
                'Premier projet Symfony en cours. Les concepts commencent à faire sens.',
                'Quelqu\'un a un bon tuto sur les Voters Symfony ? Les ACL c\'est flou pour moi.',
                'Git rebase vs git merge... encore ce débat avec les collègues.',
            ],
            'emma'   => [
                'Nouveau pipeline CI/CD en place. Les déploiements prennent 4 minutes au lieu de 20.',
                'Docker Compose est votre ami. Arrêtez d\'installer PHP, MySQL, Redis sur votre machine host.',
                'Monitoring mis en place avec Grafana. Dormir paisiblement en prod, c\'est possible.',
            ],
        ];

        /** @var Post[] $allPosts */
        $allPosts = [];
        foreach ($users as $user) {
            foreach ($postContents[$user->getUsername()] as $content) {
                $post = new Post();
                $post->setContent($content);
                $post->setAuthor($user);
                $post->setCreatedAt(new \DateTimeImmutable('-' . rand(1, 20) . ' hours -' . rand(0, 59) . ' minutes'));
                $manager->persist($post);
                $allPosts[] = $post;
            }
        }

        // ── Follows ────────────────────────────────────────────────────
        $followPairs = [
            [0, 1], [0, 2], [0, 4],
            [1, 0], [1, 3],
            [2, 0], [2, 4],
            [3, 0], [3, 1], [3, 2],
            [4, 0], [4, 1],
        ];
        foreach ($followPairs as [$from, $to]) {
            $users[$from]->addFollowing($users[$to]);
        }

        // ── Likes aléatoires ──────────────────────────────────────────
        foreach ($allPosts as $post) {
            foreach ($users as $liker) {
                if ($liker !== $post->getAuthor() && rand(0, 2) > 0) {
                    $post->addLikedBy($liker);
                }
            }
        }

        $manager->flush();

        // ── Notifications follow ──────────────────────────────────────
        foreach ($followPairs as [$from, $to]) {
            $notif = (new Notification())
                ->setRecipient($users[$to])
                ->setSender($users[$from])
                ->setType(Notification::TYPE_FOLLOW)
                ->setIsRead((bool) rand(0, 1));
            $manager->persist($notif);
        }

        // ── Notifications like ────────────────────────────────────────
        foreach ($allPosts as $post) {
            foreach ($post->getLikedBy() as $liker) {
                $notif = (new Notification())
                    ->setRecipient($post->getAuthor())
                    ->setSender($liker)
                    ->setType(Notification::TYPE_LIKE)
                    ->setIsRead((bool) rand(0, 1));
                $manager->persist($notif);
            }
        }

        $manager->flush();
    }
}
