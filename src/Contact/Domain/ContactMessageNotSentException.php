<?php

declare(strict_types=1);

namespace App\Contact\Domain;

final class ContactMessageNotSentException extends \RuntimeException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct('L’envoi a échoué. Réessayez dans quelques instants.', 0, $previous);
    }
}
