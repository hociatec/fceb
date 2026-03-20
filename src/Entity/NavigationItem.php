<?php

namespace App\Entity;

use App\Enum\NavigationItemLocation;
use App\Enum\NavigationItemType;
use App\Repository\NavigationItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: NavigationItemRepository::class)]
class NavigationItem
{
    public const AVAILABLE_ROUTE_CHOICES = [
        'Saison en cours' => 'site_current_season',
        'Actualités' => 'site_articles',
        'Calendrier' => 'site_calendar',
        'Classement' => 'site_ranking',
        'Effectif' => 'site_players',
        'Nous rejoindre' => 'site_join',
        'FAQ' => 'site_faq',
        'Partenaires' => 'site_partners_static',
        'CGU' => 'site_terms',
        'Confidentialité' => 'site_privacy',
        'Contact' => 'site_contact',
        'Bénévolat' => 'site_volunteer',
        'Séance d’essai' => 'site_trial_request',
        'Devenir partenaire' => 'site_partner_request',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 160)]
    #[Assert\NotBlank]
    private ?string $label = null;

    #[ORM\Column(enumType: NavigationItemLocation::class)]
    private NavigationItemLocation $location = NavigationItemLocation::Header;

    #[ORM\Column(enumType: NavigationItemType::class)]
    private NavigationItemType $type = NavigationItemType::Route;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $routeName = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Page $page = null;

    #[ORM\Column(length: 2048, nullable: true)]
    #[Assert\Url]
    private ?string $externalUrl = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $displayOrder = 0;

    #[ORM\Column(options: ['default' => true])]
    private bool $isEnabled = true;

    #[ORM\Column(options: ['default' => false])]
    private bool $openInNewTab = false;

    public function __toString(): string
    {
        return $this->label ?? 'Lien de navigation';
    }

    public static function availableRouteChoices(): array
    {
        return self::AVAILABLE_ROUTE_CHOICES;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getLocation(): NavigationItemLocation
    {
        return $this->location;
    }

    public function setLocation(NavigationItemLocation $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getType(): NavigationItemType
    {
        return $this->type;
    }

    public function setType(NavigationItemType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    public function setRouteName(?string $routeName): static
    {
        $this->routeName = self::normalizeOptionalText($routeName);

        return $this;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function getExternalUrl(): ?string
    {
        return $this->externalUrl;
    }

    public function setExternalUrl(?string $externalUrl): static
    {
        $this->externalUrl = self::normalizeOptionalText($externalUrl);

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

    public function isOpenInNewTab(): bool
    {
        return $this->openInNewTab;
    }

    public function setOpenInNewTab(bool $openInNewTab): static
    {
        $this->openInNewTab = $openInNewTab;

        return $this;
    }

    #[Assert\Callback]
    public function validateTarget(ExecutionContextInterface $context): void
    {
        $type = $this->type;

        if (NavigationItemType::Route === $type) {
            if (null === $this->routeName || '' === $this->routeName) {
                $context->buildViolation('Choisis une route interne pour ce lien.')
                    ->atPath('routeName')
                    ->addViolation();
            }
        }

        if (NavigationItemType::Page === $type && !$this->page instanceof Page) {
            $context->buildViolation('Choisis une page pour ce lien.')
                ->atPath('page')
                ->addViolation();
        }

        if (NavigationItemType::Url === $type && (null === $this->externalUrl || '' === $this->externalUrl)) {
            $context->buildViolation('Renseigne une URL externe pour ce lien.')
                ->atPath('externalUrl')
                ->addViolation();
        }
    }

    private static function normalizeOptionalText(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $trimmed = trim($value);

        return '' === $trimmed ? null : $trimmed;
    }
}
