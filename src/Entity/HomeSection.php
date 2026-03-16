<?php

namespace App\Entity;

use App\Repository\HomeSectionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: HomeSectionRepository::class)]
#[UniqueEntity(fields: ['sectionKey'], message: 'Cette section est déjà configurée.')]
class HomeSection
{
    public const KEY_INTRO = 'intro';
    public const KEY_FEATURED_ARTICLE = 'featured_article';
    public const KEY_NEXT_MATCH = 'next_match';
    public const KEY_UPCOMING_MATCHES = 'upcoming_matches';
    public const KEY_LAST_RESULT = 'last_result';

    public const AVAILABLE_KEYS = [
        self::KEY_INTRO => 'Présentation du club',
        self::KEY_FEATURED_ARTICLE => 'Actualité à la une',
        self::KEY_NEXT_MATCH => 'Prochain match',
        self::KEY_UPCOMING_MATCHES => 'Matchs à venir',
        self::KEY_LAST_RESULT => 'Dernier résultat',
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

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $secondaryContent = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $image = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Article $featuredArticle = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Article $secondaryArticleOne = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Article $secondaryArticleTwo = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Article $secondaryArticleThree = null;

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
                'sectionKey' => self::KEY_INTRO,
                'title' => 'Cécifoot La Bassée',
                'subtitle' => "Un club qui assume à la fois l'exigence sportive, l'accessibilité et l'ancrage local.",
                'content' => "À La Bassée, le cécifoot se construit autour d'un cadre clair : engagement, solidarité, écoute, progression et goût du jeu. Le club défend une pratique accessible, sérieuse et vivante, pensée pour accueillir durablement les joueurs, les proches, les bénévoles et les partenaires.",
                'secondaryContent' => 'Chaque visuel publié sur le site a vocation à montrer le terrain, les temps forts du collectif et la réalité de la vie du club.',
                'displayOrder' => 10,
                'isEnabled' => true,
            ],
            [
                'sectionKey' => self::KEY_FEATURED_ARTICLE,
                'title' => 'Actualité à la une',
                'subtitle' => 'Le contenu principal à retenir en ce moment, avec un angle éditorial plus net.',
                'content' => 'Les actualités doivent montrer ce qui se passe réellement au club : matchs, événements, coulisses et vie associative.',
                'secondaryContent' => 'Autres actualités',
                'displayOrder' => 20,
                'isEnabled' => true,
            ],
            [
                'sectionKey' => self::KEY_NEXT_MATCH,
                'title' => 'Prochain match',
                'subtitle' => "Le prochain rendez-vous sportif du club, avec les repères utiles en un coup d'œil.",
                'content' => null,
                'secondaryContent' => null,
                'displayOrder' => 30,
                'isEnabled' => true,
            ],
            [
                'sectionKey' => self::KEY_UPCOMING_MATCHES,
                'title' => 'Matchs à venir',
                'subtitle' => 'Les prochaines rencontres déjà programmées après le prochain rendez-vous.',
                'content' => null,
                'secondaryContent' => null,
                'displayOrder' => 40,
                'isEnabled' => true,
            ],
            [
                'sectionKey' => self::KEY_LAST_RESULT,
                'title' => 'Dernier résultat',
                'subtitle' => 'Le dernier score enregistré, avec son compte-rendu quand il existe.',
                'content' => null,
                'secondaryContent' => null,
                'displayOrder' => 50,
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getSecondaryContent(): ?string
    {
        return $this->secondaryContent;
    }

    public function setSecondaryContent(?string $secondaryContent): static
    {
        $this->secondaryContent = $secondaryContent;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getFeaturedArticle(): ?Article
    {
        return $this->featuredArticle;
    }

    public function setFeaturedArticle(?Article $featuredArticle): static
    {
        $this->featuredArticle = $featuredArticle;

        return $this;
    }

    public function getSecondaryArticleOne(): ?Article
    {
        return $this->secondaryArticleOne;
    }

    public function setSecondaryArticleOne(?Article $secondaryArticleOne): static
    {
        $this->secondaryArticleOne = $secondaryArticleOne;

        return $this;
    }

    public function getSecondaryArticleTwo(): ?Article
    {
        return $this->secondaryArticleTwo;
    }

    public function setSecondaryArticleTwo(?Article $secondaryArticleTwo): static
    {
        $this->secondaryArticleTwo = $secondaryArticleTwo;

        return $this;
    }

    public function getSecondaryArticleThree(): ?Article
    {
        return $this->secondaryArticleThree;
    }

    public function setSecondaryArticleThree(?Article $secondaryArticleThree): static
    {
        $this->secondaryArticleThree = $secondaryArticleThree;

        return $this;
    }

    /** @return list<Article> */
    public function getManualSecondaryArticles(): array
    {
        $articles = [];

        foreach ([$this->secondaryArticleOne, $this->secondaryArticleTwo, $this->secondaryArticleThree] as $article) {
            if (!$article instanceof Article) {
                continue;
            }

            $key = $article->getId() ?? spl_object_id($article);
            $articles[(string) $key] = $article;
        }

        return array_values($articles);
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

    #[Assert\Callback]
    public function validateManagedArticles(ExecutionContextInterface $context): void
    {
        $configuredArticles = array_filter([
            'featuredArticle' => $this->featuredArticle,
            'secondaryArticleOne' => $this->secondaryArticleOne,
            'secondaryArticleTwo' => $this->secondaryArticleTwo,
            'secondaryArticleThree' => $this->secondaryArticleThree,
        ]);

        if ([] === $configuredArticles) {
            return;
        }

        if (self::KEY_FEATURED_ARTICLE !== $this->sectionKey) {
            $context->buildViolation("Le choix manuel d'articles est réservé au bloc « Actualité à la une ».")
                ->atPath('featuredArticle')
                ->addViolation();

            return;
        }

        $seen = [];
        foreach ($configuredArticles as $path => $article) {
            if (!$article instanceof Article) {
                continue;
            }

            $key = (string) ($article->getId() ?? spl_object_id($article));
            if (isset($seen[$key])) {
                $context->buildViolation("Chaque article ne doit être sélectionné qu'une seule fois dans le bloc d'accueil.")
                    ->atPath($path)
                    ->addViolation();
                continue;
            }

            $seen[$key] = true;
        }
    }
}
