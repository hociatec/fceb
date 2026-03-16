<?php

namespace App\Entity;

use App\Enum\TournifySyncStatus;
use App\Repository\TournifySyncRunRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TournifySyncRunRepository::class)]
class TournifySyncRun
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Season $season = null;

    #[ORM\Column(length: 140)]
    private ?string $liveLink = null;

    #[ORM\Column(length: 80)]
    private ?string $divisionId = null;

    #[ORM\Column(length: 190)]
    private ?string $teamName = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $competition = null;

    #[ORM\Column(enumType: TournifySyncStatus::class)]
    private TournifySyncStatus $status = TournifySyncStatus::Success;

    #[ORM\Column]
    private bool $isDryRun = false;

    #[ORM\Column]
    private int $sourceMatches = 0;

    #[ORM\Column]
    private int $createdCount = 0;

    #[ORM\Column]
    private int $updatedCount = 0;

    #[ORM\Column]
    private int $removedCount = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $message = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $details = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return sprintf(
            'Synchro Tournify du %s',
            $this->createdAt?->format('d/m/Y H:i') ?? 'inconnue'
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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

    public function getLiveLink(): ?string
    {
        return $this->liveLink;
    }

    public function setLiveLink(string $liveLink): static
    {
        $this->liveLink = $liveLink;

        return $this;
    }

    public function getDivisionId(): ?string
    {
        return $this->divisionId;
    }

    public function setDivisionId(string $divisionId): static
    {
        $this->divisionId = $divisionId;

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

    public function getCompetition(): ?string
    {
        return $this->competition;
    }

    public function setCompetition(?string $competition): static
    {
        $this->competition = $competition;

        return $this;
    }

    public function getStatus(): TournifySyncStatus
    {
        return $this->status;
    }

    public function setStatus(TournifySyncStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function isDryRun(): bool
    {
        return $this->isDryRun;
    }

    public function setIsDryRun(bool $isDryRun): static
    {
        $this->isDryRun = $isDryRun;

        return $this;
    }

    public function getSourceMatches(): int
    {
        return $this->sourceMatches;
    }

    public function setSourceMatches(int $sourceMatches): static
    {
        $this->sourceMatches = $sourceMatches;

        return $this;
    }

    public function getCreatedCount(): int
    {
        return $this->createdCount;
    }

    public function setCreatedCount(int $createdCount): static
    {
        $this->createdCount = $createdCount;

        return $this;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    public function setUpdatedCount(int $updatedCount): static
    {
        $this->updatedCount = $updatedCount;

        return $this;
    }

    public function getRemovedCount(): int
    {
        return $this->removedCount;
    }

    public function setRemovedCount(int $removedCount): static
    {
        $this->removedCount = $removedCount;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getDetails(): ?array
    {
        return $this->details;
    }

    public function setDetails(?array $details): static
    {
        $this->details = $details;

        return $this;
    }
}
