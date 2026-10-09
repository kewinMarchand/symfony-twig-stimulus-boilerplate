<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Model;

final readonly class Category
{
    /**
     * @param list<Category> $children
     */
    public function __construct(
        public string $slug,
        public string $name,
        public array $children = [],
    ) {
    }

    /**
     * @return list<string>
     */
    public function leafSlugs(): array
    {
        if ([] === $this->children) {
            return [$this->slug];
        }

        return array_merge(...array_map(static fn (self $child): array => $child->leafSlugs(), $this->children));
    }
}
