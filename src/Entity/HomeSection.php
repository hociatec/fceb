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
    public const KEY_LAST_RESULT = 'last_result';

    public const AVAILABLE_KEYS = [
        self::KEY_INTRO => 'Présentation du club',
        self::KEY_FEATURED_ARTICLE => 'Actualité à la une',
        self::KEY_NEXT_MATCH => 'Prochain match',
        self::KEY_LAST_RESULT => 'Dernier résultat',
    ];

    public const AVAILABLE_TITLE_TAGS = [
        'h1' => 'H1',
        'h2' => 'H2',
        'h3' => 'H3',
    ];

    public const AVAILABLE_TEXT_ALIGNMENTS = [
        'left' => 'Gauche',
        'center' => 'Centre',
    ];

    public const AVAILABLE_LAYOUT_WIDTHS = [
        'default' => 'Standard',
        'wide' => 'Large',
    ];

    public const AVAILABLE_IMAGE_POSITIONS = [
        'start' => 'Gauche',
        'end' => 'Droite',
    ];

    public const AVAILABLE_APPEARANCES = [
        'default' => 'Standard',
        'soft' => 'Doux',
        'accent' => 'Accentué',
    ];

    public const AVAILABLE_ACCENT_TONES = [
        'green' => 'Vert',
        'gold' => 'Or',
        'ink' => 'Encre',
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

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $image = null;

    #[ORM\Column(length: 8, options: ['default' => 'h2'])]
    #[Assert\Choice(callback: [self::class, 'availableTitleTags'])]
    private string $titleTag = 'h2';

    #[ORM\Column(length: 16, options: ['default' => 'left'])]
    #[Assert\Choice(callback: [self::class, 'availableTextAlignments'])]
    private string $textAlignment = 'left';

    #[ORM\Column(length: 16, options: ['default' => 'wide'])]
    #[Assert\Choice(callback: [self::class, 'availableLayoutWidths'])]
    private string $layoutWidth = 'wide';

    #[ORM\Column(options: ['default' => true])]
    private bool $showImage = true;

    #[ORM\Column(length: 16, options: ['default' => 'start'])]
    #[Assert\Choice(callback: [self::class, 'availableImagePositions'])]
    private string $imagePosition = 'start';

    #[ORM\Column(length: 16, options: ['default' => 'default'])]
    #[Assert\Choice(callback: [self::class, 'availableAppearances'])]
    private string $appearance = 'default';

    #[ORM\Column(length: 16, options: ['default' => 'green'])]
    #[Assert\Choice(callback: [self::class, 'availableAccentTones'])]
    private string $accentTone = 'green';

    #[ORM\Column(options: ['default' => true])]
    private bool $showTag = true;

    #[ORM\Column(options: ['default' => true])]
    private bool $showMeta = true;

    #[ORM\Column(options: ['default' => true])]
    private bool $showExcerpt = true;

    #[ORM\Column(options: ['default' => true])]
    private bool $showScore = true;

    #[ORM\Column(options: ['default' => 4])]
    #[Assert\Positive]
    private int $upcomingMatchesLimit = 4;

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
        return $this->title ?? "Section d'accueil";
    }

    public static function availableSectionKeys(): array
    {
        return array_keys(self::AVAILABLE_KEYS);
    }

    public static function availableSectionChoices(): array
    {
        return array_flip(self::AVAILABLE_KEYS);
    }

    public static function availableTitleTags(): array
    {
        return array_keys(self::AVAILABLE_TITLE_TAGS);
    }

    public static function availableTitleTagChoices(): array
    {
        return array_flip(self::AVAILABLE_TITLE_TAGS);
    }

    public static function availableTextAlignments(): array
    {
        return array_keys(self::AVAILABLE_TEXT_ALIGNMENTS);
    }

    public static function availableTextAlignmentChoices(): array
    {
        return array_flip(self::AVAILABLE_TEXT_ALIGNMENTS);
    }

    public static function availableLayoutWidths(): array
    {
        return array_keys(self::AVAILABLE_LAYOUT_WIDTHS);
    }

    public static function availableLayoutWidthChoices(): array
    {
        return array_flip(self::AVAILABLE_LAYOUT_WIDTHS);
    }

    public static function availableImagePositions(): array
    {
        return array_keys(self::AVAILABLE_IMAGE_POSITIONS);
    }

    public static function availableImagePositionChoices(): array
    {
        return array_flip(self::AVAILABLE_IMAGE_POSITIONS);
    }

    public static function availableAppearances(): array
    {
        return array_keys(self::AVAILABLE_APPEARANCES);
    }

    public static function availableAppearanceChoices(): array
    {
        return array_flip(self::AVAILABLE_APPEARANCES);
    }

    public static function availableAccentTones(): array
    {
        return array_keys(self::AVAILABLE_ACCENT_TONES);
    }

    public static function availableAccentToneChoices(): array
    {
        return array_flip(self::AVAILABLE_ACCENT_TONES);
    }

    public static function defaultDefinitions(): array
    {
        return [
            [
                'sectionKey' => self::KEY_INTRO,
                'title' => 'Cécifoot La Bassée',
                'content' => "<p>À La Bassée, le cécifoot se construit autour d'un cadre clair : engagement, solidarité, écoute, progression et goût du jeu. Le club défend une pratique accessible, sérieuse et vivante, pensée pour accueillir durablement les joueurs, les proches, les bénévoles et les partenaires.</p><p>Chaque visuel publié sur le site a vocation à montrer le terrain, les temps forts du collectif et la réalité de la vie du club.</p>",
                'image' => null,
                'titleTag' => 'h2',
                'textAlignment' => 'left',
                'layoutWidth' => 'wide',
                'showImage' => true,
                'imagePosition' => 'start',
                'appearance' => 'default',
                'accentTone' => 'green',
                'showTag' => true,
                'showMeta' => true,
                'showExcerpt' => true,
                'showScore' => true,
                'upcomingMatchesLimit' => 4,
                'displayOrder' => 10,
                'isEnabled' => true,
            ],
            [
                'sectionKey' => self::KEY_FEATURED_ARTICLE,
                'title' => 'Actualité à la une',
                'content' => null,
                'image' => null,
                'titleTag' => 'h2',
                'textAlignment' => 'left',
                'layoutWidth' => 'wide',
                'showImage' => true,
                'imagePosition' => 'start',
                'appearance' => 'default',
                'accentTone' => 'green',
                'showTag' => true,
                'showMeta' => true,
                'showExcerpt' => true,
                'showScore' => true,
                'upcomingMatchesLimit' => 4,
                'displayOrder' => 40,
                'isEnabled' => true,
            ],
            [
                'sectionKey' => self::KEY_NEXT_MATCH,
                'title' => 'Prochain match',
                'content' => null,
                'image' => null,
                'titleTag' => 'h2',
                'textAlignment' => 'left',
                'layoutWidth' => 'default',
                'showImage' => true,
                'imagePosition' => 'start',
                'appearance' => 'default',
                'accentTone' => 'green',
                'showTag' => true,
                'showMeta' => true,
                'showExcerpt' => true,
                'showScore' => true,
                'upcomingMatchesLimit' => 4,
                'displayOrder' => 30,
                'isEnabled' => true,
            ],
            [
                'sectionKey' => self::KEY_LAST_RESULT,
                'title' => 'Dernier résultat',
                'content' => null,
                'image' => null,
                'titleTag' => 'h2',
                'textAlignment' => 'left',
                'layoutWidth' => 'wide',
                'showImage' => true,
                'imagePosition' => 'start',
                'appearance' => 'default',
                'accentTone' => 'green',
                'showTag' => true,
                'showMeta' => true,
                'showExcerpt' => true,
                'showScore' => true,
                'upcomingMatchesLimit' => 4,
                'displayOrder' => 20,
                'isEnabled' => true,
            ],
        ];
    }

    public function getId(): ?int { return $this->id; }
    public function getSectionKey(): ?string { return $this->sectionKey; }
    public function setSectionKey(string $sectionKey): static { $this->sectionKey = $sectionKey; return $this; }
    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }
    public function getContent(): ?string { return $this->content; }
    public function setContent(?string $content): static { $this->content = self::normalizeOptionalText($content); return $this; }
    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): static { $this->image = self::normalizeOptionalText($image); return $this; }
    public function getTitleTag(): string { return $this->titleTag; }
    public function setTitleTag(string $titleTag): static { $this->titleTag = $titleTag; return $this; }
    public function getTextAlignment(): string { return $this->textAlignment; }
    public function setTextAlignment(string $textAlignment): static { $this->textAlignment = $textAlignment; return $this; }
    public function getLayoutWidth(): string { return $this->layoutWidth; }
    public function setLayoutWidth(string $layoutWidth): static { $this->layoutWidth = $layoutWidth; return $this; }
    public function isShowImage(): bool { return $this->showImage; }
    public function setShowImage(bool $showImage): static { $this->showImage = $showImage; return $this; }
    public function getImagePosition(): string { return $this->imagePosition; }
    public function setImagePosition(string $imagePosition): static { $this->imagePosition = $imagePosition; return $this; }
    public function getAppearance(): string { return $this->appearance; }
    public function setAppearance(string $appearance): static { $this->appearance = $appearance; return $this; }
    public function getAccentTone(): string { return $this->accentTone; }
    public function setAccentTone(string $accentTone): static { $this->accentTone = $accentTone; return $this; }
    public function isShowTag(): bool { return $this->showTag; }
    public function setShowTag(bool $showTag): static { $this->showTag = $showTag; return $this; }
    public function isShowMeta(): bool { return $this->showMeta; }
    public function setShowMeta(bool $showMeta): static { $this->showMeta = $showMeta; return $this; }
    public function isShowExcerpt(): bool { return $this->showExcerpt; }
    public function setShowExcerpt(bool $showExcerpt): static { $this->showExcerpt = $showExcerpt; return $this; }
    public function isShowScore(): bool { return $this->showScore; }
    public function setShowScore(bool $showScore): static { $this->showScore = $showScore; return $this; }
    public function getUpcomingMatchesLimit(): int { return $this->upcomingMatchesLimit; }
    public function setUpcomingMatchesLimit(int $upcomingMatchesLimit): static { $this->upcomingMatchesLimit = $upcomingMatchesLimit; return $this; }
    public function getFeaturedArticle(): ?Article { return $this->featuredArticle; }
    public function setFeaturedArticle(?Article $featuredArticle): static { $this->featuredArticle = $featuredArticle; return $this; }
    public function getSecondaryArticleOne(): ?Article { return $this->secondaryArticleOne; }
    public function setSecondaryArticleOne(?Article $secondaryArticleOne): static { $this->secondaryArticleOne = $secondaryArticleOne; return $this; }
    public function getSecondaryArticleTwo(): ?Article { return $this->secondaryArticleTwo; }
    public function setSecondaryArticleTwo(?Article $secondaryArticleTwo): static { $this->secondaryArticleTwo = $secondaryArticleTwo; return $this; }
    public function getSecondaryArticleThree(): ?Article { return $this->secondaryArticleThree; }
    public function setSecondaryArticleThree(?Article $secondaryArticleThree): static { $this->secondaryArticleThree = $secondaryArticleThree; return $this; }
    public function getDisplayOrder(): int { return $this->displayOrder; }
    public function setDisplayOrder(int $displayOrder): static { $this->displayOrder = $displayOrder; return $this; }
    public function isEnabled(): bool { return $this->isEnabled; }
    public function setIsEnabled(bool $isEnabled): static { $this->isEnabled = $isEnabled; return $this; }

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

    #[Assert\Callback]
    public function validateManagedArticles(ExecutionContextInterface $context): void
    {
        $configuredArticles = array_filter([
            'featuredArticle' => $this->featuredArticle,
            'secondaryArticleOne' => $this->secondaryArticleOne,
            'secondaryArticleTwo' => $this->secondaryArticleTwo,
            'secondaryArticleThree' => $this->secondaryArticleThree,
        ]);

        if ([] !== $configuredArticles && self::KEY_FEATURED_ARTICLE !== $this->sectionKey) {
            $context->buildViolation("Le choix manuel d'articles est réservé au bloc « Actualité à la une ».")
                ->atPath('featuredArticle')
                ->addViolation();
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

    private static function normalizeOptionalText(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $trimmed = trim($value);

        return '' === $trimmed ? null : $trimmed;
    }
}


