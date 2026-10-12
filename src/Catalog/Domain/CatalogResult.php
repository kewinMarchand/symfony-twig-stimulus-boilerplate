<?php

declare(strict_types=1);

namespace App\Catalog\Domain;

final readonly class CatalogResult
{
    public function __construct(
        public ProductPage $page,
        public FacetCounts $facets,
    ) {
    }
}
