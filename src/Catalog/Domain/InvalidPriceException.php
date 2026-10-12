<?php

declare(strict_types=1);

namespace App\Catalog\Domain;

final class InvalidPriceException extends \InvalidArgumentException
{
    public function __construct(int $cents)
    {
        parent::__construct(\sprintf('Un prix ne peut pas être négatif : %d centimes.', $cents));
    }
}
