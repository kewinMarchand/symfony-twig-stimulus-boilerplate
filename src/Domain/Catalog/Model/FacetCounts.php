<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Model;

final readonly class FacetCounts
{
    /**
     * @param array<string, int> $exposures  nombre de produits par valeur d'exposition
     * @param array<string, int> $sizes      nombre de produits par taille
     * @param array<string, int> $categories nombre de produits par sous-catégorie directe
     */
    public function __construct(
        public array $exposures,
        public array $sizes,
        public array $categories,
    ) {
    }
}
