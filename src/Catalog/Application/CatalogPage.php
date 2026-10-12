<?php

declare(strict_types=1);

namespace App\Catalog\Application;

use App\Catalog\Domain\CatalogResult;
use App\Catalog\Domain\CategoryPath;

/**
 * Page du catalogue à afficher : soit les résultats, soit le message métier d'un échec de chargement.
 */
final readonly class CatalogPage
{
    private function __construct(
        public CategoryPath $categoryPath,
        public ?CatalogResult $result,
        public ?string $errorMessage,
    ) {
    }

    public static function loaded(CategoryPath $categoryPath, CatalogResult $result): self
    {
        return new self($categoryPath, $result, null);
    }

    public static function failed(CategoryPath $categoryPath, string $errorMessage): self
    {
        return new self($categoryPath, null, $errorMessage);
    }
}
