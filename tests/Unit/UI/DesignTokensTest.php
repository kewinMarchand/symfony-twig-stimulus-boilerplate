<?php

declare(strict_types=1);

namespace App\Tests\Unit\UI;

use App\UI\Styleguide\DesignTokens;
use PHPUnit\Framework\TestCase;

final class DesignTokensTest extends TestCase
{
    public function testReadsTokensOfTheFirstRootBlockOnly(): void
    {
        $css = ':root { --color-primary: #1d4ed8; --space-1: 4px; } :root[data-mode] { --color-primary: #000000; }';

        self::assertSame(['--color-primary' => '#1d4ed8'], DesignTokens::fromCss($css, 'color-'));
    }
}
