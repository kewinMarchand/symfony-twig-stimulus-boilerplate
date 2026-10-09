<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Legal;

use App\Domain\Legal\Model\AccessibilityCompliance;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AccessibilityComplianceTest extends TestCase
{
    /**
     * @return iterable<string, array{?\DateTimeImmutable, ?int, AccessibilityCompliance}>
     */
    public static function audits(): iterable
    {
        $date = new \DateTimeImmutable('2026-09-01');

        yield 'aucun audit' => [null, null, AccessibilityCompliance::NonCompliant];
        yield 'taux sans date d’audit' => [null, 80, AccessibilityCompliance::NonCompliant];
        yield 'audit sans taux' => [$date, null, AccessibilityCompliance::NonCompliant];
        yield 'moins de 50 %' => [$date, 49, AccessibilityCompliance::NonCompliant];
        yield '50 %' => [$date, 50, AccessibilityCompliance::PartiallyCompliant];
        yield '99 %' => [$date, 99, AccessibilityCompliance::PartiallyCompliant];
        yield '100 %' => [$date, 100, AccessibilityCompliance::FullyCompliant];
    }

    #[DataProvider('audits')]
    public function testComputesStatusFromAudit(?\DateTimeImmutable $auditDate, ?int $rate, AccessibilityCompliance $expected): void
    {
        self::assertSame($expected, AccessibilityCompliance::fromAudit($auditDate, $rate));
    }
}
