<?php

declare(strict_types=1);

namespace App\UI\Http\Catalog;

use App\Domain\Catalog\Model\CatalogQuery;
use App\Domain\Catalog\Model\Exposure;
use App\Domain\Catalog\Model\ProductFilters;
use App\Domain\Catalog\Model\ProductSort;
use App\Domain\Catalog\Model\Size;
use Symfony\Component\HttpFoundation\HeaderUtils;

/**
 * État du catalogue porté par la query string, seule source de vérité.
 * Les valeurs invalides sont ignorées, les valeurs par défaut absentes de l'URL.
 */
final readonly class CatalogUrlState
{
    public function __construct(
        public CatalogQuery $query = new CatalogQuery(),
        public ListView $view = ListView::Grid,
    ) {
    }

    public static function fromQueryString(?string $queryString): self
    {
        /** @var array<string, list<string>> $params parseQuery() sans crochets : une liste de valeurs par clé */
        $params = null === $queryString || '' === $queryString ? [] : HeaderUtils::parseQuery($queryString, true);
        $first = static fn (string $key): ?string => $params[$key][0] ?? null;

        $filters = new ProductFilters(
            array_values(array_filter(Exposure::cases(), static fn (Exposure $case): bool => \in_array($case->value, $params['exposition'] ?? [], true))),
            array_values(array_filter(Size::cases(), static fn (Size $case): bool => \in_array($case->value, $params['taille'] ?? [], true))),
            self::positiveInt($first('prix_min'), 0),
            self::positiveInt($first('prix_max'), 0),
            '1' === $first('en_stock'),
        );

        return new self(
            new CatalogQuery(
                $filters,
                ProductSort::tryFrom((string) $first('tri')) ?? ProductSort::Relevance,
                self::positiveInt($first('page'), 2) ?? 1,
            ),
            ListView::tryFrom((string) $first('vue')) ?? ListView::Grid,
        );
    }

    public function queryString(): string
    {
        $filters = $this->query->filters;
        $pairs = [
            ...array_map(static fn (Exposure $exposure): array => ['exposition', $exposure->value], $filters->exposures),
            ...array_map(static fn (Size $size): array => ['taille', $size->value], $filters->sizes),
            ...(null !== $filters->priceMin ? [['prix_min', (string) $filters->priceMin]] : []),
            ...(null !== $filters->priceMax ? [['prix_max', (string) $filters->priceMax]] : []),
            ...($filters->inStockOnly ? [['en_stock', '1']] : []),
            ...(ProductSort::Relevance !== $this->query->sort ? [['tri', $this->query->sort->value]] : []),
            ...(ListView::Grid !== $this->view ? [['vue', $this->view->value]] : []),
            ...($this->query->page > 1 ? [['page', (string) $this->query->page]] : []),
        ];

        if ([] === $pairs) {
            return '';
        }

        return '?'.implode('&', array_map(static fn (array $pair): string => rawurlencode($pair[0]).'='.rawurlencode($pair[1]), $pairs));
    }

    public function hasFiltersOrSort(): bool
    {
        return $this->query->filters->activeCount() > 0 || ProductSort::Relevance !== $this->query->sort;
    }

    public function withPage(int $page): self
    {
        return new self(new CatalogQuery($this->query->filters, $this->query->sort, $page), $this->view);
    }

    public function withView(ListView $view): self
    {
        return new self($this->query, $view);
    }

    public function withFilters(ProductFilters $filters): self
    {
        return new self(new CatalogQuery($filters, $this->query->sort), $this->view);
    }

    public function cleared(): self
    {
        return new self(new CatalogQuery(), $this->view);
    }

    public function withoutExposure(Exposure $exposure): self
    {
        $filters = $this->query->filters;

        return $this->withFilters(new ProductFilters(
            array_values(array_filter($filters->exposures, static fn (Exposure $value): bool => $value !== $exposure)),
            $filters->sizes, $filters->priceMin, $filters->priceMax, $filters->inStockOnly,
        ));
    }

    public function withoutSize(Size $size): self
    {
        $filters = $this->query->filters;

        return $this->withFilters(new ProductFilters(
            $filters->exposures,
            array_values(array_filter($filters->sizes, static fn (Size $value): bool => $value !== $size)),
            $filters->priceMin, $filters->priceMax, $filters->inStockOnly,
        ));
    }

    public function withoutPriceMin(): self
    {
        $filters = $this->query->filters;

        return $this->withFilters(new ProductFilters($filters->exposures, $filters->sizes, null, $filters->priceMax, $filters->inStockOnly));
    }

    public function withoutPriceMax(): self
    {
        $filters = $this->query->filters;

        return $this->withFilters(new ProductFilters($filters->exposures, $filters->sizes, $filters->priceMin, null, $filters->inStockOnly));
    }

    public function withoutInStock(): self
    {
        $filters = $this->query->filters;

        return $this->withFilters(new ProductFilters($filters->exposures, $filters->sizes, $filters->priceMin, $filters->priceMax, false));
    }

    private static function positiveInt(?string $value, int $min): ?int
    {
        if (null === $value || 1 !== preg_match('/^\d{1,6}$/', $value)) {
            return null;
        }

        $int = (int) $value;

        return $int >= $min ? $int : null;
    }
}
