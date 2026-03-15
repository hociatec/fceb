<?php

namespace App\Entity;

use App\Repository\RankingEntryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RankingEntryRepository::class)]
#[ORM\Table(name: 'ranking_entry', uniqueConstraints: [new ORM\UniqueConstraint(name: 'uniq_ranking_entry_season_team', columns: ['season_id', 'team_name'])])]
#[UniqueEntity(fields: ['season', 'teamName'], message: 'Cette équipe est déjà présente dans le classement de la saison sélectionnée.')]
class RankingEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'rankingEntries')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    private ?Season $season = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    private ?string $teamName = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $points = 0;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $wins = 0;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $losses = 0;

    #[ORM\Column]
    private int $goalDifference = 0;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $displayOrder = 0;

    public function __toString(): string
    {
        return $this->teamName ?? 'Équipe';
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTeamName(): ?string
    {
        return $this->teamName;
    }

    public function setTeamName(string $teamName): static
    {
        $this->teamName = $teamName;

        return $this;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): static
    {
        $this->points = $points;

        return $this;
    }

    public function getWins(): int
    {
        return $this->wins;
    }

    public function setWins(int $wins): static
    {
        $this->wins = $wins;

        return $this;
    }

    public function getLosses(): int
    {
        return $this->losses;
    }

    public function setLosses(int $losses): static
    {
        $this->losses = $losses;

        return $this;
    }

    public function getGoalDifference(): int
    {
        return $this->goalDifference;
    }

    public function setGoalDifference(int $goalDifference): static
    {
        $this->goalDifference = $goalDifference;

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
}
