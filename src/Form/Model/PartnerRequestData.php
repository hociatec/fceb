<?php

namespace App\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class PartnerRequestData
{
    #[Assert\NotBlank(message: 'Le nom de la structure est obligatoire.')]
    #[Assert\Length(max: 160, maxMessage: 'Le nom de la structure ne peut pas dépasser {{ limit }} caractères.')]
    public ?string $organization = null;

    #[Assert\NotBlank(message: 'Le nom du contact est obligatoire.')]
    #[Assert\Length(max: 120, maxMessage: 'Le nom du contact ne peut pas dépasser {{ limit }} caractères.')]
    public ?string $contactName = null;

    #[Assert\NotBlank(message: 'L’adresse e-mail est obligatoire.')]
    #[Assert\Email(message: 'Merci de renseigner une adresse e-mail valide.')]
    public ?string $email = null;

    #[Assert\Length(max: 30, maxMessage: 'Le téléphone ne peut pas dépasser {{ limit }} caractères.')]
    public ?string $phone = null;

    #[Assert\NotBlank(message: 'Merci de préciser le type de partenariat.')]
    public ?string $supportType = null;

    #[Assert\Url(message: 'Merci de renseigner une URL valide.')]
    public ?string $website = null;

    #[Assert\NotBlank(message: 'Merci de préciser votre demande.')]
    #[Assert\Length(min: 10, minMessage: 'Le message doit contenir au moins {{ limit }} caractères.')]
    public ?string $message = null;
}
