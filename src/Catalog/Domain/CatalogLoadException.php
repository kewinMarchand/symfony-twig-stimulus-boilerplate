<?php

declare(strict_types=1);

namespace App\Catalog\Domain;

final class CatalogLoadException extends \RuntimeException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct('Impossible de charger les produits pour le moment.', 0, $previous);
    }
}
