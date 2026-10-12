<?php

declare(strict_types=1);

namespace App\Catalog\UI;

/**
 * Pages à afficher dans la pagination, null pour une ellipse : toutes jusqu'à 7, sinon 1, courante ± 1
 * et dernière. Règle de présentation, pas du modèle.
 */
final class PaginationWindow
{
    private const int MAX_VISIBLE_PAGES = 7;

    /**
     * @return list<int|null>
     */
    public static function pages(int $current, int $pageCount): array
    {
        if ($pageCount <= self::MAX_VISIBLE_PAGES) {
            return range(1, $pageCount);
        }

        $start = max(2, $current - 1);
        $end = min($pageCount - 1, $current + 1);

        return [
            1,
            ...($start > 2 ? [null] : []),
            ...range($start, $end),
            ...($end < $pageCount - 1 ? [null] : []),
            $pageCount,
        ];
    }
}
