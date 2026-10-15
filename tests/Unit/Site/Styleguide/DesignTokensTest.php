<?php

declare(strict_types=1);

namespace App\Tests\Unit\Site\Styleguide;

use App\Site\Styleguide\DesignTokens;
use PHPUnit\Framework\TestCase;

final class DesignTokensTest extends TestCase
{
    public function testReadsTokensOfTheFirstRootBlockOnly(): void
    {
        $css = ':root { --color-primary: #1d4ed8; --space-1: 4px; } :root[data-mode] { --color-primary: #000000; }';

        self::assertSame(['--color-primary' => '#1d4ed8'], DesignTokens::fromCss($css, 'color-'));
    }

    public function testResolvesAliasesOfTheLibraryTokens(): void
    {
        $css = ':root { --color-primary: var(--bui-color-primary); --color-dark: #111827; --color-other: var(--bui-unknown); }';
        $library = ':root { --bui-color-primary: #1d4ed8; } [data-a11y-mode="enhanced"] { --bui-color-primary: #1e3a8a; }';

        self::assertSame(
            ['--color-primary' => '#1d4ed8', '--color-dark' => '#111827', '--color-other' => 'var(--bui-unknown)'],
            DesignTokens::fromCss($css, 'color-', $library),
        );
    }
}
