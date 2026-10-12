<?php

declare(strict_types=1);

namespace App\Tests\Unit\Catalog\Domain;

use App\Catalog\Domain\InvalidPriceException;
use App\Catalog\Domain\Price;
use PHPUnit\Framework\TestCase;

final class PriceTest extends TestCase
{
    public function testConvertsEurosOfTheUrlIntoCents(): void
    {
        self::assertSame(1500, Price::fromEuros(15)->cents);
        self::assertSame(29, Price::fromCents(2990)->euros());
    }

    public function testFormatsInFrench(): void
    {
        self::assertSame("29,90\u{a0}€", Price::fromCents(2990)->format());
    }

    public function testRejectsANegativeAmount(): void
    {
        $this->expectException(InvalidPriceException::class);
        $this->expectExceptionMessage('ne peut pas être négatif');

        Price::fromCents(-1);
    }
}
