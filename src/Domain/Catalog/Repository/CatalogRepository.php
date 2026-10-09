<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Repository;

use App\Domain\Catalog\Exception\CatalogLoadException;
use App\Domain\Catalog\Model\CatalogQuery;
use App\Domain\Catalog\Model\CatalogResult;
use App\Domain\Catalog\Model\Category;
use App\Domain\Catalog\Model\CategoryTree;

interface CatalogRepository
{
    public function categoryTree(): CategoryTree;

    /**
     * @throws CatalogLoadException
     */
    public function search(Category $category, CatalogQuery $query): CatalogResult;
}
