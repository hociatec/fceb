<?php

namespace App\Entity;

use App\Enum\MatchStatus;
use App\Repository\MatchGameRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MatchGameRepository::class)]
class MatchGame
{
    public const CLUB_NAME = 'Cécifoot 59 La Bassée';

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
    private ?string $competition = null;

    #[ORM\Column(length: 160, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $matchDate = null;

    #[ORM\Column(length: 20)]
    private string $side = 'home';

    #[ORM\Column(nullable: true)]
    private ?int $ourScore = null;

    #[ORM\Column(nullable: true)]
    private ?int $opponentScore = null;

    #[ORM\Column(enumType: MatchStatus::class)]
    private MatchStatus $status = MatchStatus::Scheduled;

    #[ORM\ManyToOne(targetEntity: Season::class, inversedBy: 'matches')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Season $season = null;

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
        $this->competition = $competition;

        return $this;
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
