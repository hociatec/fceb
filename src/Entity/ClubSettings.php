<?php

namespace App\Entity;

use App\Repository\ClubSettingsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClubSettingsRepository::class)]
class ClubSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 160)]
    #[Assert\NotBlank]
    private ?string $clubName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $publicEmail = null;

    #[ORM\Column(length: 60)]
    #[Assert\NotBlank]
    private ?string $phone = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $address = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Url]
    private ?string $mapUrl = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $homeIntroTitle = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $homeIntroSubtitle = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $homeIntroLead = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $homeIntroMediaNote = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $homeFeaturedTitle = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $homeFeaturedSubtitle = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $homeUpcomingTitle = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $homeUpcomingSubtitle = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $homeLastResultTitle = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $homeLastResultSubtitle = null;

    public function __toString(): string
    {
        return $this->clubName ?? 'Paramètres du club';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClubName(): ?string
    {
        return $this->clubName;
    }

    public function setClubName(string $clubName): static
    {
        $this->clubName = $clubName;

        return $this;
    }

    public function getPublicEmail(): ?string
    {
        return $this->publicEmail;
    }

    public function setPublicEmail(string $publicEmail): static
    {
        $this->publicEmail = $publicEmail;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getMapUrl(): ?string
    {
        return $this->mapUrl;
    }

    public function setMapUrl(string $mapUrl): static
    {
        $this->mapUrl = $mapUrl;

        return $this;
    }

    public function getHomeIntroTitle(): ?string
    {
        return $this->homeIntroTitle;
    }

    public function setHomeIntroTitle(?string $homeIntroTitle): static
    {
        $this->homeIntroTitle = $homeIntroTitle;

        return $this;
    }

    public function getHomeIntroSubtitle(): ?string
    {
        return $this->homeIntroSubtitle;
    }

    public function setHomeIntroSubtitle(?string $homeIntroSubtitle): static
    {
        $this->homeIntroSubtitle = $homeIntroSubtitle;

        return $this;
    }

    public function getHomeIntroLead(): ?string
    {
        return $this->homeIntroLead;
    }

    public function setHomeIntroLead(?string $homeIntroLead): static
    {
        $this->homeIntroLead = $homeIntroLead;

        return $this;
    }

    public function getHomeIntroMediaNote(): ?string
    {
        return $this->homeIntroMediaNote;
    }

    public function setHomeIntroMediaNote(?string $homeIntroMediaNote): static
    {
        $this->homeIntroMediaNote = $homeIntroMediaNote;

        return $this;
    }

    public function getHomeFeaturedTitle(): ?string
    {
        return $this->homeFeaturedTitle;
    }

    public function setHomeFeaturedTitle(?string $homeFeaturedTitle): static
    {
        $this->homeFeaturedTitle = $homeFeaturedTitle;

        return $this;
    }

    public function getHomeFeaturedSubtitle(): ?string
    {
        return $this->homeFeaturedSubtitle;
    }

    public function setHomeFeaturedSubtitle(?string $homeFeaturedSubtitle): static
    {
        $this->homeFeaturedSubtitle = $homeFeaturedSubtitle;

        return $this;
    }

    public function getHomeUpcomingTitle(): ?string
    {
        return $this->homeUpcomingTitle;
    }

    public function setHomeUpcomingTitle(?string $homeUpcomingTitle): static
    {
        $this->homeUpcomingTitle = $homeUpcomingTitle;

        return $this;
    }

    public function getHomeUpcomingSubtitle(): ?string
    {
        return $this->homeUpcomingSubtitle;
    }

    public function setHomeUpcomingSubtitle(?string $homeUpcomingSubtitle): static
    {
        $this->homeUpcomingSubtitle = $homeUpcomingSubtitle;

        return $this;
    }

    public function getHomeLastResultTitle(): ?string
    {
        return $this->homeLastResultTitle;
    }

    public function setHomeLastResultTitle(?string $homeLastResultTitle): static
    {
        $this->homeLastResultTitle = $homeLastResultTitle;

        return $this;
    }

    public function getHomeLastResultSubtitle(): ?string
    {
        return $this->homeLastResultSubtitle;
    }

    public function setHomeLastResultSubtitle(?string $homeLastResultSubtitle): static
    {
        $this->homeLastResultSubtitle = $homeLastResultSubtitle;

        return $this;
    }
}
