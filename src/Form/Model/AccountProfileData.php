<?php

namespace App\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AccountProfileData
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

    public ?string $newPassword = null;

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if (null === $this->newPassword || '' === $this->newPassword) {
            return;
        }

        if (mb_strlen($this->newPassword) < 8) {
            $context->buildViolation('Le nouveau mot de passe doit contenir au moins 8 caractères.')
                ->atPath('newPassword')
                ->addViolation();
        }
    }
}
