<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Model;

final readonly class CatalogResult
{
    public function __construct(
        public ProductPage $page,
        public FacetCounts $facets,
    ) {
    }
}
