<?php

declare(strict_types=1);

namespace App\Catalog\Domain;

final readonly class ProductFilters
{
    /**
     * @param list<Exposure> $exposures
     * @param list<Size>     $sizes
     */
    public function __construct(
        public array $exposures = [],
        public array $sizes = [],
        public ?Price $priceMin = null,
        public ?Price $priceMax = null,
        public bool $inStockOnly = false,
    ) {
    }

    public function activeCount(): int
    {
        return \count($this->exposures) + \count($this->sizes)
            + (null !== $this->priceMin ? 1 : 0) + (null !== $this->priceMax ? 1 : 0)
            + ($this->inStockOnly ? 1 : 0);
    }

    public function withoutExposures(): self
    {
        return new self([], $this->sizes, $this->priceMin, $this->priceMax, $this->inStockOnly);
    }

    public function withoutSizes(): self
    {
        return new self($this->exposures, [], $this->priceMin, $this->priceMax, $this->inStockOnly);
    }
}
