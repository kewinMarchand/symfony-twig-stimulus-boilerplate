<?php

declare(strict_types=1);

namespace App\Contact\Domain;

/**
 * Le destinataire a refusé le message, champ par champ. Les messages sont métier et en français :
 * ils s'affichent tels quels sous les champs concernés.
 */
final class ContactMessageRejectedException extends \RuntimeException
{
    /**
     * @param list<array{field: string, message: string}> $violations
     */
    public function __construct(public readonly array $violations)
    {
        parent::__construct('Le message a été refusé, vérifiez les champs signalés.');
    }
}
