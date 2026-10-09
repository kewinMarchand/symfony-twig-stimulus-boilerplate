<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Model;

enum Exposure: string
{
    case Sun = 'soleil';
    case PartialShade = 'mi-ombre';
    case Shade = 'ombre';

    public function label(): string
    {
        return match ($this) {
            self::Sun => 'Soleil',
            self::PartialShade => 'Mi-ombre',
            self::Shade => 'Ombre',
        };
    }
}
