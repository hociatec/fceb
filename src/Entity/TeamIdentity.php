<?php

namespace App\Entity;

use App\Repository\TeamIdentityRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TeamIdentityRepository::class)]
#[UniqueEntity(fields: ['teamName'], message: 'Cette équipe existe déjà.')]
class TeamIdentity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 160, unique: true)]
    #[Assert\NotBlank]
    private ?string $teamName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logoPath = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $aliases = null;

    public function __toString(): string
    {
        return $this->teamName ?? 'Équipe';
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLogoPath(): ?string
    {
        return $this->logoPath;
    }

    public function setLogoPath(?string $logoPath): static
    {
        $this->logoPath = $logoPath;

        return $this;
    }

    public function getAliases(): ?string
    {
        return $this->aliases;
    }

    public function setAliases(?string $aliases): static
    {
        $this->aliases = $aliases;

        return $this;
    }
}
