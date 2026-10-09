<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ErrorPagesTest extends WebTestCase
{
    public function testUnknownRouteRendersCustom404(): void
    {
        $client = self::createClient(['debug' => false]);
        $client->request('GET', '/route-inexistante');

        self::assertResponseStatusCodeSame(404);
        self::assertSelectorTextSame('h1', 'Page introuvable');
        self::assertSelectorExists('meta[name="robots"][content="noindex"]');
        self::assertSelectorTextSame('[data-testid="layout-breadcrumb"] [aria-current="page"]', 'Page introuvable');
    }

    public function testErrorPreviewRendersCustom500WithoutTechnicalDetails(): void
    {
        $client = self::createClient();
        $client->request('GET', '/_error/500');

        self::assertResponseStatusCodeSame(500);
        self::assertSelectorTextSame('h1', 'Une erreur est survenue');
        $text = $client->getCrawler()->filter('main')->text();
        self::assertStringNotContainsString('Exception', $text);
        self::assertStringNotContainsString('stack', $text);
    }

    public function testStyleguideIsAvailableOutsideProduction(): void
    {
        $client = self::createClient();
        $client->request('GET', '/charte-graphique');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h1', 'Charte graphique');
        self::assertSelectorExists('meta[name="robots"][content="noindex, nofollow"]');
        self::assertSelectorCount(10, 'main section h2');
    }
}
