<?php

namespace App\Entity;

use App\Repository\SeasonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SeasonRepository::class)]
#[UniqueEntity(fields: ['slug'], message: 'Ce slug existe deja.')]
class Season
{
    public function __toString(): string
    {
        return $this->name ?? 'Saison';
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 140, unique: true)]
    #[Assert\NotBlank]
    private ?string $slug = null;

    #[ORM\Column(type: 'date_immutable')]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(type: 'date_immutable')]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column]
    private bool $isCurrent = false;

    /** @var Collection<int, Article> */
    #[ORM\OneToMany(mappedBy: 'season', targetEntity: Article::class)]
    private Collection $articles;

    /** @var Collection<int, MatchGame> */
    #[ORM\OneToMany(mappedBy: 'season', targetEntity: MatchGame::class)]
    private Collection $matches;

    /** @var Collection<int, RankingEntry> */
    #[ORM\OneToMany(mappedBy: 'season', targetEntity: RankingEntry::class)]
    private Collection $rankingEntries;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->matches = new ArrayCollection();
        $this->rankingEntries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function isCurrent(): bool
    {
        return $this->isCurrent;
    }

    public function setIsCurrent(bool $isCurrent): static
    {
        $this->isCurrent = $isCurrent;

        return $this;
    }

    /** @return Collection<int, Article> */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    /** @return Collection<int, MatchGame> */
    public function getMatches(): Collection
    {
        return $this->matches;
    }

    /** @return Collection<int, RankingEntry> */
    public function getRankingEntries(): Collection
    {
        return $this->rankingEntries;
    }
}
