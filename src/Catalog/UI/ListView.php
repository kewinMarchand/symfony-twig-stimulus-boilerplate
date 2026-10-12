<?php

declare(strict_types=1);

namespace App\Catalog\UI;

enum ListView: string
{
    case Grid = 'grille';
    case List = 'liste';
}
