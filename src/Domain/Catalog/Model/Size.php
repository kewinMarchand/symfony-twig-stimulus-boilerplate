<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Model;

enum Size: string
{
    case Small = 'S';
    case Medium = 'M';
    case Large = 'L';

    public function label(): string
    {
        return match ($this) {
            self::Small => 'Petite (S)',
            self::Medium => 'Moyenne (M)',
            self::Large => 'Grande (L)',
        };
    }
}
