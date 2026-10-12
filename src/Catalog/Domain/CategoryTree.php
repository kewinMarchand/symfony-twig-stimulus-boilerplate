<?php

declare(strict_types=1);

namespace App\Catalog\Domain;

final readonly class CategoryTree
{
    public function __construct(public Category $root)
    {
    }
}
