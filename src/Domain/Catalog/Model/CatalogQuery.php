<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Model;

final readonly class CatalogQuery
{
    public const int PER_PAGE = 12;

    public function __construct(
        public ProductFilters $filters = new ProductFilters(),
        public ProductSort $sort = ProductSort::Relevance,
        public int $page = 1,
    ) {
    }
}
