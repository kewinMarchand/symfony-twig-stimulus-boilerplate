<?php

declare(strict_types=1);

namespace App\Tests\Unit\UI\Seo;

use App\UI\Seo\Breadcrumb;
use PHPUnit\Framework\TestCase;

final class BreadcrumbTest extends TestCase
{
    public function testHomeHasNoBreadcrumb(): void
    {
        $breadcrumb = Breadcrumb::fromTrail([]);

        self::assertTrue($breadcrumb->isEmpty());
        self::assertNull($breadcrumb->jsonLd('https://exemple.fr', 'https://exemple.fr/'));
    }

    public function testStartsWithHomeAndLeavesTheLastItemWithoutLink(): void
    {
        $breadcrumb = Breadcrumb::fromTrail([
            ['label' => 'Catalogue', 'path' => '/catalogue'],
            ['label' => 'Feuillages', 'path' => '/catalogue/plantes-interieur/feuillages'],
        ]);

        self::assertSame(['Accueil', 'Catalogue', 'Feuillages'], array_map(static fn ($item): string => $item->label, $breadcrumb->items));
        self::assertSame(['/', '/catalogue', null], array_map(static fn ($item): ?string => $item->path, $breadcrumb->items));
    }

    public function testBuildsBreadcrumbListJsonLdWithAbsoluteUrls(): void
    {
        $jsonLd = Breadcrumb::fromTrail([['label' => 'Contact']])->jsonLd('https://exemple.fr', 'https://exemple.fr/contact');

        self::assertNotNull($jsonLd);
        self::assertSame('BreadcrumbList', $jsonLd['@type']);
        self::assertSame([
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => 'https://exemple.fr/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Contact', 'item' => 'https://exemple.fr/contact'],
        ], $jsonLd['itemListElement']);
    }
}
