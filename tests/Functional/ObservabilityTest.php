<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Sentry\State\HubInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class ObservabilityTest extends WebTestCase
{
    private const string FAKE_DSN = 'http://clefactice@localhost:3231/1';

    protected function tearDown(): void
    {
        unset($_ENV['GLITCHTIP_DSN'], $_SERVER['GLITCHTIP_DSN']);
        $_ENV['GLITCHTIP_DSN'] = $_SERVER['GLITCHTIP_DSN'] = '';
        parent::tearDown();
    }

    public function testWithoutDsnNothingIsInjected(): void
    {
        $client = self::createClient();
        $client->request('GET', '/');

        $html = (string) $client->getResponse()->getContent();
        self::assertStringNotContainsString('glitchtip', $html);
        self::assertStringNotContainsString('sentry-browser', $html);
    }

    public function testWithDsnTheBrowserControllerIsInjected(): void
    {
        $_ENV['GLITCHTIP_DSN'] = $_SERVER['GLITCHTIP_DSN'] = self::FAKE_DSN;
        $client = self::createClient();
        $crawler = $client->request('GET', '/');

        $body = $crawler->filter('body');
        self::assertSame('glitchtip', $body->attr('data-controller'));
        self::assertSame(self::FAKE_DSN, $body->attr('data-glitchtip-dsn-value'));
        self::assertSame('test', $body->attr('data-glitchtip-environment-value'));
        self::assertStringContainsString('sentry-browser', (string) $body->attr('data-glitchtip-bundle-value'));
    }

    public function testServerSdkSendsNoPersonalDataAndIgnoresExpectedErrors(): void
    {
        self::bootKernel();
        $options = self::getContainer()->get(HubInterface::class)->getClient()?->getOptions();

        self::assertNotNull($options);
        self::assertFalse($options->shouldSendDefaultPii());
        self::assertSame('none', $options->getMaxRequestBodySize());
        self::assertNull($options->getTracesSampleRate());
        foreach ([NotFoundHttpException::class, UnprocessableEntityHttpException::class, ServiceUnavailableHttpException::class] as $expected) {
            self::assertContains($expected, $options->getIgnoreExceptions());
        }
    }
}
