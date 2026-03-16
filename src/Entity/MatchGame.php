<?php

namespace App\Entity;

use App\Enum\MatchStatus;
use App\Repository\MatchGameRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: MatchGameRepository::class)]
class MatchGame
{
    public const CLUB_NAME = 'Cécifoot 59 La Bassée';
    public const COMPETITION_CHAMPIONNAT = 'Championnat';
    public const COMPETITION_COUPE_DE_FRANCE = 'Coupe de France';

    public function __toString(): string
    {
        return $this->getPoster();
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 160)]
    #[Assert\NotBlank]
    private ?string $opponent = null;

    #[ORM\Column(length: 120, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    private ?string $competition = null;

    #[ORM\Column(length: 160, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $matchDate = null;

    #[ORM\Column(length: 20)]
    private string $side = 'home';

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    private ?int $ourScore = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    private ?int $opponentScore = null;

    #[ORM\Column(enumType: MatchStatus::class)]
    private MatchStatus $status = MatchStatus::Scheduled;

    #[ORM\ManyToOne(targetEntity: Season::class, inversedBy: 'matches')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Season $season = null;

    #[ORM\OneToOne(inversedBy: 'linkedMatch')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Article $linkedArticle = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOpponent(): ?string
    {
        return $this->opponent;
    }

    public function setOpponent(string $opponent): static
    {
        $this->opponent = $opponent;

        return $this;
    }

    public function getCompetition(): ?string
    {
        return $this->competition;
    }

    public function setCompetition(?string $competition): static
    {
        $this->competition = self::normalizeCompetitionLabel($competition);

        return $this;
    }

    public static function normalizeCompetitionLabel(?string $competition): ?string
    {
        if (null === $competition) {
            return null;
        }

        $competition = trim(preg_replace('/\s+/', ' ', $competition) ?? '');

        if ('' === $competition) {
            return null;
        }

        return match (mb_strtolower($competition)) {
            'championnat',
            'championnat b1 challenger',
            'championnat de france b1 challenger' => self::COMPETITION_CHAMPIONNAT,
            'coupe de france' => self::COMPETITION_COUPE_DE_FRANCE,
            default => $competition,
        };
    }

    /** @return array<string, string> */
    public static function competitionChoices(): array
    {
        return [
            self::COMPETITION_CHAMPIONNAT => self::COMPETITION_CHAMPIONNAT,
            self::COMPETITION_COUPE_DE_FRANCE => self::COMPETITION_COUPE_DE_FRANCE,
        ];
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getMatchDate(): ?\DateTimeImmutable
    {
        return $this->matchDate;
    }

    public function setMatchDate(\DateTimeImmutable $matchDate): static
    {
        $this->matchDate = $matchDate;

        return $this;
    }

    public function getSide(): string
    {
        return $this->side;
    }

    public function setSide(string $side): static
    {
        $this->side = $side;

        return $this;
    }

    public function getOurScore(): ?int
    {
        return $this->ourScore;
    }

    public function setOurScore(?int $ourScore): static
    {
        $this->ourScore = $ourScore;

        return $this;
    }

    public function getOpponentScore(): ?int
    {
        return $this->opponentScore;
    }

    public function setOpponentScore(?int $opponentScore): static
    {
        $this->opponentScore = $opponentScore;

        return $this;
    }

    public function getStatus(): MatchStatus
    {
        return $this->status;
    }

    public function setStatus(MatchStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getSeason(): ?Season
    {
        return $this->season;
    }

    public function setSeason(Season $season): static
    {
        $this->season = $season;

        return $this;
    }

    public function getLinkedArticle(): ?Article
    {
        return $this->linkedArticle;
    }

    public function setLinkedArticle(?Article $linkedArticle): static
    {
        if ($this->linkedArticle === $linkedArticle) {
            return $this;
        }

        $previousLinkedArticle = $this->linkedArticle;
        $this->linkedArticle = $linkedArticle;

        if ($previousLinkedArticle instanceof Article && $previousLinkedArticle->getLinkedMatch() === $this) {
            $previousLinkedArticle->setLinkedMatch(null);
        }

        if ($linkedArticle instanceof Article && $linkedArticle->getLinkedMatch() !== $this) {
            $linkedArticle->setLinkedMatch($this);
        }

        return $this;
    }

    #[Assert\Callback]
    public function validateCompletedMatchScores(ExecutionContextInterface $context): void
    {
        if (MatchStatus::Completed !== $this->status) {
            return;
        }

        if (null === $this->ourScore) {
            $context->buildViolation('Le score du club est obligatoire quand le match est terminé.')
                ->atPath('ourScore')
                ->addViolation();
        }

        if (null === $this->opponentScore) {
            $context->buildViolation("Le score de l'adversaire est obligatoire quand le match est terminé.")
                ->atPath('opponentScore')
                ->addViolation();
        }
    }

    public function getHomeTeamName(): string
    {
        return 'home' === $this->side ? self::CLUB_NAME : ($this->opponent ?? 'Adversaire');
    }

    public function getAwayTeamName(): string
    {
        return 'home' === $this->side ? ($this->opponent ?? 'Adversaire') : self::CLUB_NAME;
    }

    public function getPoster(): string
    {
        return sprintf('%s vs %s', $this->getHomeTeamName(), $this->getAwayTeamName());
    }
}
