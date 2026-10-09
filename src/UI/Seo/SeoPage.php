<?php

declare(strict_types=1);

namespace App\UI\Seo;

final readonly class SeoPage
{
    /**
     * @param list<string> $jsonLd scripts JSON-LD déjà encodés
     */
    public function __construct(
        public string $title,
        public string $description,
        public ?string $canonical,
        public string $robots,
        public string $image,
        public string $imageAlt,
        public Breadcrumb $breadcrumb,
        public array $jsonLd,
        public ?string $prev = null,
        public ?string $next = null,
    ) {
    }
}
