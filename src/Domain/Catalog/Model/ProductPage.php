<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Model;

final readonly class ProductPage
{
    /**
     * @param list<Product> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $pageCount,
    ) {
    }
}
