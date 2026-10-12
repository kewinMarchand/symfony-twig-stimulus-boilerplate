<?php

declare(strict_types=1);

namespace App\Catalog\Domain;

interface CatalogRepository
{
    public function categoryTree(): CategoryTree;

    /**
     * @throws CatalogLoadException
     */
    public function search(Category $category, CatalogQuery $query): CatalogResult;
}
