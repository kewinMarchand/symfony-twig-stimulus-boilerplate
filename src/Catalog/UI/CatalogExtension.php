<?php

declare(strict_types=1);

namespace App\Catalog\UI;

use App\Catalog\Domain\CatalogRepository;
use App\Catalog\Domain\CatalogSearch;
use App\Catalog\Domain\CategoryTree;
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
     * @return list<int|null>
     */
    #[AsTwigFunction('pagination_window')]
    public function paginationWindow(int $current, int $pageCount): array
    {
        return CatalogSearch::paginationWindow($current, $pageCount);
    }

    #[AsTwigFilter('price')]
    public function price(int $cents): string
    {
        $formatter = new \NumberFormatter('fr_FR', \NumberFormatter::CURRENCY);

        return (string) $formatter->formatCurrency($cents / 100, 'EUR');
    }
}
