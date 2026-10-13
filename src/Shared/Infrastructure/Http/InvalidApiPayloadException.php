<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http;

final class InvalidApiPayloadException extends \UnexpectedValueException
{
    public function __construct(string $path, string $expected)
    {
        parent::__construct(\sprintf('Réponse de l’API inattendue : %s devrait être %s.', $path, $expected));
    }
}
