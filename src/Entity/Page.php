<?php

namespace App\Entity;

use App\Enum\PagePlacement;
use App\Enum\ContentStatus;
use App\Repository\PageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[UniqueEntity(fields: ['slug'], message: 'Ce slug existe deja.')]
class Page
{
    public function __toString(): string
    {
        return $this->title ?? 'Page';
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 160)]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank]
    private ?string $slug = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $metaTitle = null;

    #[ORM\Column(length: 320, nullable: true)]
    private ?string $metaDescription = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $heroImage = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private ?string $content = null;

    #[ORM\Column(enumType: PagePlacement::class)]
    private PagePlacement $placement = PagePlacement::None;

    #[ORM\Column]
    private bool $isPublished = true;

    #[ORM\Column(enumType: ContentStatus::class)]
    private ContentStatus $status = ContentStatus::Published;

    #[ORM\Column]
    private int $menuOrder = 0;

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

    public function getHeroImage(): ?string
    {
        return $this->heroImage;
    }

    public function setHeroImage(?string $heroImage): static
    {
        $this->heroImage = $heroImage;

        return $this;
    }

    public function getPlacement(): PagePlacement
    {
        return $this->placement;
    }

    public function setPlacement(PagePlacement $placement): static
    {
        $this->placement = $placement;

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

    public function getMenuOrder(): int
    {
        return $this->menuOrder;
    }

    public function setMenuOrder(int $menuOrder): static
    {
        $this->menuOrder = $menuOrder;

        return $this;
    }
}
