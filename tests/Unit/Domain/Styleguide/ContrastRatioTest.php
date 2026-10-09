<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Styleguide;

use App\Domain\Styleguide\Model\ContrastRatio;
use PHPUnit\Framework\TestCase;

final class ContrastRatioTest extends TestCase
{
    public function testBlackOnWhiteIsMaximal(): void
    {
        $ratio = ContrastRatio::between('#000000', '#ffffff');

        self::assertSame(21.0, $ratio->value);
        self::assertSame('AAA', $ratio->level());
    }

    public function testPrimaryOnWhiteIsAa(): void
    {
        $ratio = ContrastRatio::between('#1d4ed8', '#FFFFFF');

        self::assertEqualsWithDelta(6.7, $ratio->value, 0.05);
        self::assertSame('AA', $ratio->level());
    }

    public function testLightGreyOnWhiteIsInsufficient(): void
    {
        self::assertSame('insuffisant', ContrastRatio::between('#9ca3af', '#ffffff')->level());
    }

    public function testRejectsShortHex(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        ContrastRatio::between('#fff', '#000000');
    }
}
