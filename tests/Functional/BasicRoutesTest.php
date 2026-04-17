<?php

namespace App\Tests\Functional;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BasicRoutesTest extends WebTestCase
{
    public function testHomepageReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
    }

    public function testNewPostRedirectsToLoginWhenUnauthenticated(): void
    {
        $client = static::createClient();
        $client->request('POST', '/post/nouveau', ['content' => 'test content']);

        $this->assertResponseRedirects('/login');
    }

    public function testAuthenticatedUserCanAccessPostForm(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine.orm.entity_manager');

        $email = 'test_' . uniqid() . '@example.com';
        $user = (new User())
            ->setEmail($email)
            ->setUsername('testuser_' . uniqid())
            ->setPassword('hashed')
            ->setCreatedAt(new \DateTimeImmutable());

        $em->persist($user);
        $em->flush();

        $client->loginUser($user);
        $client->request('GET', '/post/nouveau');

        $this->assertResponseIsSuccessful();
    }

    public function testApiPostsReturnsValidJson(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/posts', [], [], ['HTTP_ACCEPT' => 'application/json']);

        $this->assertResponseIsSuccessful();

        $content = $client->getResponse()->getContent();
        $data = json_decode($content, true);
        $this->assertNotNull($data, 'Response should be valid JSON');
        $this->assertIsArray($data);
    }
}
