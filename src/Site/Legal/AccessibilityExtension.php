<?php

declare(strict_types=1);

namespace App\Site\Legal;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Attribute\AsTwigFunction;

final readonly class AccessibilityExtension
{
    /**
     * @param array{audit_date: ?string, compliance_rate: ?int, auditor: ?string} $accessibility
     */
    public function __construct(
        #[Autowire('%site.accessibility%')]
        private array $accessibility,
    ) {
    }

    #[AsTwigFunction('accessibility_compliance')]
    public function compliance(): AccessibilityCompliance
    {
        $auditDate = $this->accessibility['audit_date'];

        return AccessibilityCompliance::fromAudit(
            null === $auditDate ? null : new \DateTimeImmutable($auditDate),
            $this->accessibility['compliance_rate'],
        );
    }
}
