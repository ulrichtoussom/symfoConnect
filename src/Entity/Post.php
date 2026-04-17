<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post as ApiPost;
use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['post:list']],
            paginationItemsPerPage: 10,
        ),
        new Get(
            normalizationContext: ['groups' => ['post:read']],
        ),
        new ApiPost(
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            denormalizationContext: ['groups' => ['post:write']],
            normalizationContext: ['groups' => ['post:read']],
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'content'         => 'partial',
    'author.username' => 'partial',
])]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['post:list', 'post:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['post:list', 'post:read', 'post:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 5)]
    private ?string $content = null;

    #[ORM\Column]
    #[Groups(['post:list', 'post:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['post:list', 'post:read'])]
    private ?User $author = null;

    /** @var Collection<int, User> */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'likedPosts')]
    private Collection $likedBy;

    public function __construct()
    {
        $this->likedBy    = new ArrayCollection();
        $this->createdAt  = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getContent(): ?string { return $this->content; }
    public function setContent(string $content): static { $this->content = $content; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getAuthor(): ?User { return $this->author; }
    public function setAuthor(?User $author): static { $this->author = $author; return $this; }

    /** @return Collection<int, User> */
    public function getLikedBy(): Collection { return $this->likedBy; }

    public function addLikedBy(User $user): static
    {
        if (!$this->likedBy->contains($user)) {
            $this->likedBy->add($user);
        }
        return $this;
    }

    public function removeLikedBy(User $user): static
    {
        $this->likedBy->removeElement($user);
        return $this;
    }
}
