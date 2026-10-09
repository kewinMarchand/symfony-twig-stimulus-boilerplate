<?php

declare(strict_types=1);

namespace App\UI\Http\Form;

use App\Domain\Contact\Model\ContactMessage;
use Symfony\Component\Validator\Constraints as Assert;

final class ContactFormData
{
    #[Assert\Length(min: 2, max: 100, minMessage: 'Indiquez votre nom (2 caractères minimum).', maxMessage: 'Votre nom ne doit pas dépasser 100 caractères.', normalizer: 'trim')]
    public string $name = '';

    #[Assert\NotBlank(message: 'Indiquez une adresse e-mail valide, par exemple nom@domaine.fr.')]
    #[Assert\Email(message: 'Indiquez une adresse e-mail valide, par exemple nom@domaine.fr.')]
    public string $email = '';

    #[Assert\Length(min: 10, max: 5000, minMessage: 'Votre message doit contenir au moins 10 caractères.', maxMessage: 'Votre message ne doit pas dépasser 5000 caractères.', normalizer: 'trim')]
    public string $message = '';

    public function toMessage(): ContactMessage
    {
        return new ContactMessage($this->name, $this->email, $this->message);
    }
}
