<?php

declare(strict_types=1);

namespace App\Tests\Unit\Catalog\Infrastructure\InMemory;

use App\Catalog\Domain\Category;
use App\Catalog\Domain\Exposure;
use App\Catalog\Domain\Price;
use App\Catalog\Domain\Product;
use App\Catalog\Domain\ProductFilters;
use App\Catalog\Domain\ProductSort;
use App\Catalog\Domain\Size;
use App\Catalog\Infrastructure\InMemory\CatalogSearch;
use PHPUnit\Framework\TestCase;

final class CatalogSearchTest extends TestCase
{
    /**
     * @return list<Product>
     */
    private static function products(): array
    {
        return [
            new Product('1', 'a', 'Bégonia', 'interieur', Price::fromCents(1500), Exposure::Shade, Size::Small, true, 1),
            new Product('2', 'b', 'Agave', 'exterieur', Price::fromCents(4000), Exposure::Sun, Size::Large, true, 2),
            new Product('3', 'c', 'Calathea', 'interieur', Price::fromCents(2500), Exposure::PartialShade, Size::Medium, false, 3),
            new Product('4', 'd', 'Dracaena', 'interieur', Price::fromCents(3500), Exposure::PartialShade, Size::Large, true, 4),
            new Product('5', 'e', 'Échinacée', 'exterieur', Price::fromCents(900), Exposure::Sun, Size::Small, true, 5),
        ];
    }

    private static function root(): Category
    {
        return new Category('', 'Catalogue', [new Category('interieur', 'Intérieur'), new Category('exterieur', 'Extérieur')]);
    }

    /**
     * @param list<Product> $products
     *
     * @return list<string>
     */
    private static function ids(array $products): array
    {
        return array_map(static fn (Product $product): string => $product->id, $products);
    }

    public function testFiltersByCategoryLeaves(): void
    {
        self::assertSame(['1', '3', '4'], self::ids(CatalogSearch::inCategories(self::products(), ['interieur'])));
    }

    public function testCombinesFacetsWithAndBetweenGroupsAndOrWithinAGroup(): void
    {
        $filters = new ProductFilters([Exposure::Sun, Exposure::Shade], [Size::Small]);

        self::assertSame(['1', '5'], self::ids(CatalogSearch::filter(self::products(), $filters)));
    }

    public function testFiltersByPriceInEurosAndStock(): void
    {
        $filters = new ProductFilters(priceMin: Price::fromEuros(15), priceMax: Price::fromEuros(35), inStockOnly: true);

        self::assertSame(['1', '4'], self::ids(CatalogSearch::filter(self::products(), $filters)));
    }

    public function testCountsFacetsDisjunctively(): void
    {
        $filters = new ProductFilters([Exposure::Sun], [Size::Small]);

        $counts = CatalogSearch::countFacets(self::products(), self::root(), $filters);

        self::assertSame(['soleil' => 1, 'mi-ombre' => 0, 'ombre' => 1], $counts->exposures);
        self::assertSame(['S' => 1, 'M' => 0, 'L' => 1], $counts->sizes);
        self::assertSame(['interieur' => 0, 'exterieur' => 1], $counts->categories);
    }

    public function testSortsByPriceAndName(): void
    {
        self::assertSame(['5', '1', '3', '4', '2'], self::ids(CatalogSearch::sort(self::products(), ProductSort::PriceAscending)));
        self::assertSame(['2', '4', '3', '1', '5'], self::ids(CatalogSearch::sort(self::products(), ProductSort::PriceDescending)));
        self::assertSame(['1', '2', '3', '4', '5'], self::ids(CatalogSearch::sort(self::products(), ProductSort::Relevance)));
    }

    public function testPaginates(): void
    {
        $page = CatalogSearch::paginate(self::products(), 2, 2);

        self::assertSame(['3', '4'], self::ids($page->items));
        self::assertSame(5, $page->total);
        self::assertSame(3, $page->pageCount);
    }

    public function testEmptyResultHasOnePage(): void
    {
        self::assertSame(1, CatalogSearch::paginate([], 1, 12)->pageCount);
    }
}
