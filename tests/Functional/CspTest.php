<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CspTest extends WebTestCase
{
    public function testEveryPageCarriesTheReportOnlyHeader(): void
    {
        $client = self::createClient();
        $client->request('GET', '/mentions-legales');

        $csp = (string) $client->getResponse()->headers->get('Content-Security-Policy-Report-Only');
        self::assertStringContainsString("default-src 'self'", $csp);
        self::assertStringContainsString('report-uri /api/csp-report', $csp);
        self::assertFalse($client->getResponse()->headers->has('Content-Security-Policy'));
    }

    public function testTheReportRouteRejectsInvalidJson(): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/csp-report', server: ['CONTENT_TYPE' => 'application/csp-report'], content: '{"csp-report":');

        self::assertResponseStatusCodeSame(400);
    }

    public function testTheReportRouteRejectsATooLongBody(): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/csp-report', server: ['CONTENT_TYPE' => 'application/csp-report'], content: str_repeat('a', 10_001));

        self::assertResponseStatusCodeSame(413);
    }
}
