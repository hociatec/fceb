<?php

namespace App\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class TrialRequestData
{
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(max: 120, maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')]
    public ?string $name = null;

    #[Assert\NotBlank(message: 'L’adresse e-mail est obligatoire.')]
    #[Assert\Email(message: 'Merci de renseigner une adresse e-mail valide.')]
    public ?string $email = null;

    #[Assert\Length(max: 30, maxMessage: 'Le téléphone ne peut pas dépasser {{ limit }} caractères.')]
    public ?string $phone = null;

    #[Assert\NotBlank(message: 'Merci de préciser votre profil.')]
    public ?string $profile = null;

    #[Assert\Length(max: 255, maxMessage: 'Les disponibilités ne peuvent pas dépasser {{ limit }} caractères.')]
    public ?string $availability = null;

    #[Assert\Length(max: 2000, maxMessage: 'Le message ne peut pas dépasser {{ limit }} caractères.')]
    public ?string $message = null;
}
