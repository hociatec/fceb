<?php

namespace App\Entity;

use App\Repository\HomeSectionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: HomeSectionRepository::class)]
#[UniqueEntity(fields: ['sectionKey'], message: 'Cette section est déjà configurée.')]
class HomeSection
{
    public const KEY_LATEST_ARTICLE = 'latest_article';
    public const KEY_UPCOMING_MATCHES = 'upcoming_matches';
    public const KEY_LAST_MATCH = 'last_match';

    public const AVAILABLE_KEYS = [
        self::KEY_LATEST_ARTICLE => 'Dernière actualité',
        self::KEY_UPCOMING_MATCHES => 'Matchs à venir',
        self::KEY_LAST_MATCH => 'Dernier match',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 80, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Choice(callback: [self::class, 'availableSectionKeys'])]
    private ?string $sectionKey = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $subtitle = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $displayOrder = 0;

    #[ORM\Column]
    private bool $isEnabled = true;

    public function __toString(): string
    {
        return $this->title ?? 'Section d’accueil';
    }

    public static function availableSectionKeys(): array
    {
        return array_keys(self::AVAILABLE_KEYS);
    }

    public static function availableSectionChoices(): array
    {
        return array_flip(self::AVAILABLE_KEYS);
    }

    public static function defaultDefinitions(): array
    {
        return [
            [
                'sectionKey' => self::KEY_LATEST_ARTICLE,
                'title' => 'Dernière actualité',
                'subtitle' => 'Le dernier article mis en avant par le club.',
                'displayOrder' => 10,
                'isEnabled' => true,
            ],
            [
                'sectionKey' => self::KEY_UPCOMING_MATCHES,
                'title' => 'Matchs à venir',
                'subtitle' => 'Les prochaines rencontres déjà programmées.',
                'displayOrder' => 20,
                'isEnabled' => true,
            ],
            [
                'sectionKey' => self::KEY_LAST_MATCH,
                'title' => 'Dernier match',
                'subtitle' => 'Le dernier résultat disponible du club.',
                'displayOrder' => 30,
                'isEnabled' => true,
            ],
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSectionKey(): ?string
    {
        return $this->sectionKey;
    }

    public function setSectionKey(string $sectionKey): static
    {
        $this->sectionKey = $sectionKey;

        return $this;
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

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): static
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    public function getDisplayOrder(): int
    {
        return $this->displayOrder;
    }

    public function setDisplayOrder(int $displayOrder): static
    {
        $this->displayOrder = $displayOrder;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): static
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }
}
