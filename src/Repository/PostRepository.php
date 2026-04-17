<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Post> */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /** @return Post[] */
    public function findFeedForUser(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.author', 'a')->addSelect('a')
            ->leftJoin('p.likedBy', 'l')->addSelect('l')
            ->where('a IN (:following)')
            ->setParameter('following', $user->getFollowing())
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()->getResult();
    }

    /** @return Post[] */
    public function findLatest(int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.author', 'a')->addSelect('a')
            ->leftJoin('p.likedBy', 'l')->addSelect('l')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }
}
