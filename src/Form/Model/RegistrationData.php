<?php

namespace App\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class RegistrationData
{
    #[Assert\NotBlank(message: 'Le nom complet est obligatoire.')]
    #[Assert\Length(
        max: 120,
        maxMessage: 'Le nom complet ne peut pas dépasser {{ limit }} caractères.'
    )]
    public ?string $fullName = null;

    #[Assert\NotBlank(message: 'L’adresse e-mail est obligatoire.')]
    #[Assert\Email(message: 'Merci de renseigner une adresse e-mail valide.')]
    public ?string $email = null;

    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire.')]
    #[Assert\Length(
        min: 8,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.'
    )]
    public ?string $plainPassword = null;
}
