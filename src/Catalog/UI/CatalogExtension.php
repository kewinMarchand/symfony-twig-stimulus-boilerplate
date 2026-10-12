<?php

declare(strict_types=1);

namespace App\Catalog\UI;

use App\Catalog\Domain\CatalogRepository;
use App\Catalog\Domain\CategoryPath;
use App\Catalog\Domain\CategoryTree;
use App\Catalog\Domain\Price;
use Twig\Attribute\AsTwigFilter;
use Twig\Attribute\AsTwigFunction;

final readonly class CatalogExtension
{
    public function __construct(private CatalogRepository $catalogRepository)
    {
    }

    #[AsTwigFunction('catalog_tree')]
    public function tree(): CategoryTree
    {
        return $this->catalogRepository->categoryTree();
    }

    /**
     * @return list<CategoryPath>
     */
    #[AsTwigFunction('catalog_paths')]
    public function paths(): array
    {
        return CategoryPath::all($this->catalogRepository->categoryTree());
    }

    /**
     * @return list<int|null>
     */
    #[AsTwigFunction('pagination_window')]
    public function paginationWindow(int $current, int $pageCount): array
    {
        return PaginationWindow::pages($current, $pageCount);
    }

    #[AsTwigFilter('price')]
    public function price(Price|int $price): string
    {
        return ($price instanceof Price ? $price : Price::fromCents($price))->format();
    }
}
