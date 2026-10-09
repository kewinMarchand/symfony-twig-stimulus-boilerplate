<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Exception;

final class CatalogLoadException extends \RuntimeException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct('Impossible de charger les produits pour le moment.', 0, $previous);
    }
}
