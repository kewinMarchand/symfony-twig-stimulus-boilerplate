<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\InMemory;

use App\Catalog\Domain\CatalogQuery;
use App\Catalog\Domain\CatalogResult;
use App\Catalog\Domain\Category;
use App\Catalog\Domain\Exposure;
use App\Catalog\Domain\FacetCounts;
use App\Catalog\Domain\Product;
use App\Catalog\Domain\ProductFilters;
use App\Catalog\Domain\ProductPage;
use App\Catalog\Domain\ProductSort;
use App\Catalog\Domain\Size;

/**
 * Simulateur de l'API : filtre, comptage disjonctif, tri et pagination des résultats. C'est le travail du
 * fournisseur de données, pas une règle du modèle : au branchement de l'API, ce fichier disparaît avec
 * le dépôt en mémoire, sans toucher au domaine.
 */
final class CatalogSearch
{
    /**
     * @param list<Product> $products
     */
    public static function search(array $products, Category $category, CatalogQuery $query): CatalogResult
    {
        $inCategory = self::inCategories($products, $category->leafSlugs());
        $filtered = self::filter($inCategory, $query->filters);

        return new CatalogResult(
            self::paginate(self::sort($filtered, $query->sort), $query->page, CatalogQuery::PER_PAGE),
            self::countFacets($inCategory, $category, $query->filters),
        );
    }

    /**
     * @param list<Product> $products
     * @param list<string>  $categorySlugs
     *
     * @return list<Product>
     */
    public static function inCategories(array $products, array $categorySlugs): array
    {
        return array_values(array_filter($products, static fn (Product $product): bool => \in_array($product->categorySlug, $categorySlugs, true)));
    }

    /**
     * @param list<Product> $products
     *
     * @return list<Product>
     */
    public static function filter(array $products, ProductFilters $filters): array
    {
        return array_values(array_filter($products, static fn (Product $product): bool => ([] === $filters->exposures || \in_array($product->exposure, $filters->exposures, true))
            && ([] === $filters->sizes || \in_array($product->size, $filters->sizes, true))
            && (null === $filters->priceMin || $product->price->cents >= $filters->priceMin->cents)
            && (null === $filters->priceMax || $product->price->cents <= $filters->priceMax->cents)
            && (!$filters->inStockOnly || $product->inStock)));
    }

    /**
     * Comptage disjonctif : chaque facette est comptée sur les produits filtrés par toutes les autres.
     *
     * @param list<Product> $products produits de la catégorie courante
     */
    public static function countFacets(array $products, Category $category, ProductFilters $filters): FacetCounts
    {
        $byExposure = self::filter($products, $filters->withoutExposures());
        $bySize = self::filter($products, $filters->withoutSizes());
        $filtered = self::filter($products, $filters);

        $exposures = [];
        foreach (Exposure::cases() as $exposure) {
            $exposures[$exposure->value] = \count(array_filter($byExposure, static fn (Product $product): bool => $product->exposure === $exposure));
        }

        $sizes = [];
        foreach (Size::cases() as $size) {
            $sizes[$size->value] = \count(array_filter($bySize, static fn (Product $product): bool => $product->size === $size));
        }

        $categories = [];
        foreach ($category->children as $child) {
            $categories[$child->slug] = \count(self::inCategories($filtered, $child->leafSlugs()));
        }

        return new FacetCounts($exposures, $sizes, $categories);
    }

    /**
     * @param list<Product> $products
     *
     * @return list<Product>
     */
    public static function sort(array $products, ProductSort $sort): array
    {
        $comparator = match ($sort) {
            ProductSort::Relevance => null,
            ProductSort::PriceAscending => static fn (Product $a, Product $b): int => $a->price->cents <=> $b->price->cents,
            ProductSort::PriceDescending => static fn (Product $a, Product $b): int => $b->price->cents <=> $a->price->cents,
            ProductSort::Name => static fn (Product $a, Product $b): int => strcoll($a->name, $b->name),
        };

        if (null !== $comparator) {
            usort($products, $comparator);
        }

        return $products;
    }

    /**
     * @param list<Product> $products
     */
    public static function paginate(array $products, int $page, int $perPage): ProductPage
    {
        $total = \count($products);

        return new ProductPage(
            \array_slice($products, ($page - 1) * $perPage, $perPage),
            $total,
            $page,
            max(1, (int) ceil($total / $perPage)),
        );
    }
}
