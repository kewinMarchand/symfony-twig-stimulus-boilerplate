<?php

declare(strict_types=1);

namespace App\UI\Http\Catalog;

enum ListView: string
{
    case Grid = 'grille';
    case List = 'liste';
}
