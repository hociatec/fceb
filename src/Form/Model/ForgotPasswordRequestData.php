<?php

namespace App\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ForgotPasswordRequestData
{
    #[Assert\NotBlank(message: 'Merci de renseigner votre adresse e-mail.')]
    #[Assert\Email(message: 'Merci de renseigner une adresse e-mail valide.')]
    public ?string $email = null;
}
