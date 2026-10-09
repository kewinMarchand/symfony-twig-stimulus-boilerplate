<?php

declare(strict_types=1);

namespace App\Domain\Legal\Model;

enum AccessibilityCompliance: string
{
    case NonCompliant = 'non conforme';
    case PartiallyCompliant = 'partiellement conforme';
    case FullyCompliant = 'totalement conforme';

    private const int PARTIAL_THRESHOLD = 50;
    private const int FULL_RATE = 100;

    public static function fromAudit(?\DateTimeImmutable $auditDate, ?int $complianceRate): self
    {
        return match (true) {
            null === $auditDate, null === $complianceRate, $complianceRate < self::PARTIAL_THRESHOLD => self::NonCompliant,
            $complianceRate >= self::FULL_RATE => self::FullyCompliant,
            default => self::PartiallyCompliant,
        };
    }
}
