<?php

namespace App\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class VolunteerRequestData
{
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(max: 120, maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')]
    public ?string $name = null;

    #[Assert\NotBlank(message: 'L’adresse e-mail est obligatoire.')]
    #[Assert\Email(message: 'Merci de renseigner une adresse e-mail valide.')]
    public ?string $email = null;

    #[Assert\Length(max: 30, maxMessage: 'Le téléphone ne peut pas dépasser {{ limit }} caractères.')]
    public ?string $phone = null;

    #[Assert\Length(max: 255, maxMessage: 'Les disponibilités ne peuvent pas dépasser {{ limit }} caractères.')]
    public ?string $availability = null;

    #[Assert\Length(max: 255, maxMessage: 'Les compétences ne peuvent pas dépasser {{ limit }} caractères.')]
    public ?string $skills = null;

    #[Assert\NotBlank(message: 'Merci de préciser votre demande.')]
    #[Assert\Length(min: 10, minMessage: 'Le message doit contenir au moins {{ limit }} caractères.')]
    public ?string $message = null;
}
