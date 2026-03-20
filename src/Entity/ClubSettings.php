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

    #[ORM\Column(name: 'singleton_key', type: 'smallint', unique: true, options: ['default' => 1])]
    private int $singletonKey = 1;

    #[ORM\Column(length: 160, nullable: true)]
    #[Assert\Length(max: 160)]
    private ?string $footerBadge = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $footerHeadline = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $footerText = null;

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

    public function getSingletonKey(): int
    {
        return $this->singletonKey;
    }

    public function getFooterBadge(): ?string
    {
        return $this->footerBadge;
    }

    public function setFooterBadge(?string $footerBadge): static
    {
        $this->footerBadge = $footerBadge;

        return $this;
    }

    public function getFooterHeadline(): ?string
    {
        return $this->footerHeadline;
    }

    public function setFooterHeadline(?string $footerHeadline): static
    {
        $this->footerHeadline = $footerHeadline;

        return $this;
    }

    public function getFooterText(): ?string
    {
        return $this->footerText;
    }

    public function setFooterText(?string $footerText): static
    {
        $this->footerText = $footerText;

        return $this;
    }
}
