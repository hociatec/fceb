<?php

namespace App\Entity;

use App\Enum\ArticlePlacement;
use App\Enum\ContentStatus;
use App\Repository\ArticleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[UniqueEntity(fields: ['slug'], message: 'Ce slug existe deja.')]
class Article
{
    public function __toString(): string
    {
        return $this->title ?? 'Article';
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(length: 190, unique: true)]
    #[Assert\NotBlank]
    private ?string $slug = null;

    #[ORM\Column(length: 400, nullable: true)]
    private ?string $excerpt = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $metaTitle = null;

    #[ORM\Column(length: 320, nullable: true)]
    private ?string $metaDescription = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coverImage = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private ?string $content = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column]
    private bool $isPublished = true;

    #[ORM\Column(enumType: ContentStatus::class)]
    private ContentStatus $status = ContentStatus::Published;

    #[ORM\Column(enumType: ArticlePlacement::class)]
    private ArticlePlacement $placement = ArticlePlacement::None;

    #[ORM\ManyToOne(targetEntity: Season::class, inversedBy: 'articles')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Season $season = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $author = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getExcerpt(): ?string
    {
        return $this->excerpt;
    }

    public function setExcerpt(?string $excerpt): static
    {
        $this->excerpt = $excerpt;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): static
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): static
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCoverImage(?string $coverImage): static
    {
        $this->coverImage = $coverImage;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function isPublished(): bool
    {
        return ContentStatus::Published === $this->status;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;
        $this->status = $isPublished ? ContentStatus::Published : ContentStatus::Draft;

        return $this;
    }

    public function getStatus(): ContentStatus
    {
        return $this->status;
    }

    public function setStatus(ContentStatus $status): static
    {
        $this->status = $status;
        $this->isPublished = ContentStatus::Published === $status;

        return $this;
    }

    public function getPlacement(): ArticlePlacement
    {
        return $this->placement;
    }

    public function setPlacement(ArticlePlacement $placement): static
    {
        $this->placement = $placement;

        return $this;
    }

    public function getSeason(): ?Season
    {
        return $this->season;
    }

    public function setSeason(?Season $season): static
    {
        $this->season = $season;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }
}
