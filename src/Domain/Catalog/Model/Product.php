<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Model;

final readonly class Product
{
    public function __construct(
        public string $id,
        public string $slug,
        public string $name,
        public string $categorySlug,
        public int $price,
        public Exposure $exposure,
        public Size $size,
        public bool $inStock,
        public int $image,
    ) {
    }
}
