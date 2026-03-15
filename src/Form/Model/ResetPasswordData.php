<?php

namespace App\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ResetPasswordData
{
    #[Assert\NotBlank(message: 'Le nouveau mot de passe est obligatoire.')]
    #[Assert\Length(
        min: 8,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.'
    )]
    public ?string $plainPassword = null;
}
