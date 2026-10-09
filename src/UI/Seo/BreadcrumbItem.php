<?php

declare(strict_types=1);

namespace App\UI\Seo;

final readonly class BreadcrumbItem
{
    public function __construct(
        public string $label,
        public ?string $path,
    ) {
    }
}
