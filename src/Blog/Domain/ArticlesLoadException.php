<?php

declare(strict_types=1);

namespace App\Blog\Domain;

final class ArticlesLoadException extends \RuntimeException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct('Impossible de charger les articles pour le moment.', 0, $previous);
    }
}
