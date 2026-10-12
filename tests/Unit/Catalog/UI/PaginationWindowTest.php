<?php

declare(strict_types=1);

namespace App\Tests\Unit\Catalog\UI;

use App\Catalog\UI\PaginationWindow;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PaginationWindowTest extends TestCase
{
    /**
     * @return iterable<string, array{int, int, list<int|null>}>
     */
    public static function windows(): iterable
    {
        yield 'une page' => [1, 1, [1]];
        yield 'sept pages, sans ellipse' => [4, 7, [1, 2, 3, 4, 5, 6, 7]];
        yield 'début' => [1, 10, [1, 2, null, 10]];
        yield 'milieu' => [5, 10, [1, null, 4, 5, 6, null, 10]];
        yield 'avant-dernière' => [9, 10, [1, null, 8, 9, 10]];
        yield 'troisième, sans ellipse à gauche' => [3, 10, [1, 2, 3, 4, null, 10]];
    }

    /**
     * @param list<int|null> $expected
     */
    #[DataProvider('windows')]
    public function testPaginationWindow(int $current, int $pageCount, array $expected): void
    {
        self::assertSame($expected, PaginationWindow::pages($current, $pageCount));
    }
}
