<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Model;

enum ProductSort: string
{
    case Relevance = 'pertinence';
    case PriceAscending = 'prix-asc';
    case PriceDescending = 'prix-desc';
    case Name = 'nom';

    public function label(): string
    {
        return match ($this) {
            self::Relevance => 'Pertinence',
            self::PriceAscending => 'Prix croissant',
            self::PriceDescending => 'Prix décroissant',
            self::Name => 'Nom',
        };
    }
}
