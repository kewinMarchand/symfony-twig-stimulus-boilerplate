<?php

declare(strict_types=1);

namespace App\UI\Twig;

use App\UI\Seo\SeoPage;
use App\UI\Seo\SeoPageFactory;
use Twig\Attribute\AsTwigFunction;

final readonly class SeoExtension
{
    public function __construct(private SeoPageFactory $factory)
    {
    }

    /**
     * @param array{
     *     title?: string,
     *     description?: string,
     *     breadcrumb?: list<array{label: string, path?: string}>,
     *     canonical?: string|false,
     *     robots?: string,
     *     json_ld?: list<array<string, mixed>>,
     *     prev?: string|null,
     *     next?: string|null,
     * } $options
     */
    #[AsTwigFunction('seo_page')]
    public function seoPage(array $options = []): SeoPage
    {
        return $this->factory->create($options);
    }
}
