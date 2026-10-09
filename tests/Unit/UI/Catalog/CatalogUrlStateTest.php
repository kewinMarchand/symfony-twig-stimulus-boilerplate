<?php

declare(strict_types=1);

namespace App\Tests\Unit\UI\Catalog;

use App\Domain\Catalog\Model\Exposure;
use App\Domain\Catalog\Model\ProductSort;
use App\Domain\Catalog\Model\Size;
use App\UI\Http\Catalog\CatalogUrlState;
use App\UI\Http\Catalog\ListView;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CatalogUrlStateTest extends TestCase
{
    public function testParsesRepeatedKeysAndEveryParameter(): void
    {
        $state = CatalogUrlState::fromQueryString('exposition=soleil&exposition=mi-ombre&taille=L&prix_min=10&prix_max=50&en_stock=1&tri=prix-asc&vue=liste&page=2');

        $filters = $state->query->filters;
        self::assertSame([Exposure::Sun, Exposure::PartialShade], $filters->exposures);
        self::assertSame([Size::Large], $filters->sizes);
        self::assertSame(10, $filters->priceMin);
        self::assertSame(50, $filters->priceMax);
        self::assertTrue($filters->inStockOnly);
        self::assertSame(ProductSort::PriceAscending, $state->query->sort);
        self::assertSame(ListView::List, $state->view);
        self::assertSame(2, $state->query->page);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function canonicalQueries(): iterable
    {
        yield 'vide' => [''];
        yield 'facettes' => ['?exposition=soleil&exposition=mi-ombre&taille=S'];
        yield 'tout' => ['?exposition=ombre&taille=M&taille=L&prix_min=5&prix_max=80&en_stock=1&tri=nom&vue=liste&page=3'];
    }

    #[DataProvider('canonicalQueries')]
    public function testRoundTrip(string $query): void
    {
        self::assertSame($query, CatalogUrlState::fromQueryString(ltrim($query, '?'))->queryString());
    }

    public function testIgnoresInvalidAndDefaultValues(): void
    {
        $state = CatalogUrlState::fromQueryString('exposition=lune&taille=XL&prix_min=-3&prix_max=abc&en_stock=oui&tri=pertinence&vue=grille&page=1&inconnu=1');

        self::assertSame('', $state->queryString());
        self::assertFalse($state->hasFiltersOrSort());
    }

    public function testNormalisesOrderAndEmptyFormFields(): void
    {
        $state = CatalogUrlState::fromQueryString('taille=S&exposition=ombre&prix_min=&prix_max=&tri=');

        self::assertSame('?exposition=ombre&taille=S', $state->queryString());
    }

    public function testChangingFiltersResetsThePageAndKeepsTheView(): void
    {
        $state = CatalogUrlState::fromQueryString('exposition=soleil&taille=S&vue=liste&page=2');

        self::assertSame('?taille=S&vue=liste', $state->withoutExposure(Exposure::Sun)->queryString());
        self::assertSame('?vue=liste', $state->cleared()->queryString());
        self::assertSame('?exposition=soleil&taille=S&page=2', $state->withView(ListView::Grid)->queryString());
    }
}
