<?php

namespace App\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ContactData
{
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(
        max: 120,
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.'
    )]
    public ?string $name = null;

    #[Assert\NotBlank(message: 'L’adresse e-mail est obligatoire.')]
    #[Assert\Email(message: 'Merci de renseigner une adresse e-mail valide.')]
    public ?string $email = null;

    #[Assert\Length(
        max: 150,
        maxMessage: 'L’objet ne peut pas dépasser {{ limit }} caractères.'
    )]
    public ?string $subject = null;

    #[Assert\NotBlank(message: 'Le message est obligatoire.')]
    #[Assert\Length(
        min: 10,
        minMessage: 'Le message doit contenir au moins {{ limit }} caractères.'
    )]
    public ?string $message = null;
}
