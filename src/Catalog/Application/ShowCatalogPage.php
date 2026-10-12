<?php

declare(strict_types=1);

namespace App\Catalog\Application;

use App\Catalog\Domain\CatalogLoadException;
use App\Catalog\Domain\CatalogQuery;
use App\Catalog\Domain\CatalogRepository;
use App\Catalog\Domain\CategoryPath;

/**
 * Cas d'utilisation « afficher une page du catalogue » : résout la catégorie, interroge le dépôt, puis
 * décide. Catégorie inconnue ou page au-delà de la dernière : null, que l'interface traduit en 404.
 * Échec du dépôt : page en erreur, que l'interface affiche avec une relance.
 */
final readonly class ShowCatalogPage
{
    public function __construct(private CatalogRepository $catalogRepository)
    {
    }

    public function __invoke(string $path, CatalogQuery $query): ?CatalogPage
    {
        $categoryPath = CategoryPath::resolve($this->catalogRepository->categoryTree(), $path);
        if (null === $categoryPath) {
            return null;
        }

        try {
            $result = $this->catalogRepository->search($categoryPath->category(), $query);
        } catch (CatalogLoadException $exception) {
            return CatalogPage::failed($categoryPath, $exception->getMessage());
        }

        if ($query->page > $result->page->pageCount) {
            return null;
        }

        return CatalogPage::loaded($categoryPath, $result);
    }
}
