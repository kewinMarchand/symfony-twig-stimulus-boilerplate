<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Model;

final readonly class CategoryTree
{
    public function __construct(public Category $root)
    {
    }

    /**
     * Branche de la racine à la catégorie désignée par ses slugs, racine exclue.
     *
     * @param list<string> $slugs
     *
     * @return list<Category>|null null si un slug est inconnu
     */
    public function trail(array $slugs): ?array
    {
        $trail = [];
        $current = $this->root;

        foreach ($slugs as $slug) {
            $next = array_find($current->children, static fn (Category $child): bool => $child->slug === $slug);
            if (null === $next) {
                return null;
            }
            $trail[] = $next;
            $current = $next;
        }

        return $trail;
    }

    /**
     * @return list<list<Category>> toutes les branches, de chaque catégorie jusqu'à la racine exclue
     */
    public function allTrails(): array
    {
        return self::trailsBelow($this->root, []);
    }

    /**
     * @param list<Category> $parents
     *
     * @return list<list<Category>>
     */
    private static function trailsBelow(Category $category, array $parents): array
    {
        $trails = [];
        foreach ($category->children as $child) {
            $trail = [...$parents, $child];
            $trails = [...$trails, $trail, ...self::trailsBelow($child, $trail)];
        }

        return $trails;
    }
}
