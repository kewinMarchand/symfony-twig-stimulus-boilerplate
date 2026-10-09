<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Domain\Home\Exception\ArticlesLoadException;
use App\Domain\Home\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomeControllerTest extends WebTestCase
{
    public function testRendersHeroCarouselAndBlog(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/');

        self::assertSelectorExists('[data-testid="home-hero"] img[fetchpriority="high"]');
        self::assertNull($crawler->filter('[data-testid="home-hero"] img')->attr('loading'));
        self::assertSelectorCount(5, '[data-testid="carousel-slide"]');
        self::assertSelectorCount(3, '[data-testid="home-blog-card"]');
        self::assertSelectorCount(3, '[data-testid="home-blog-card"] time[datetime]');
    }

    public function testShowsAFriendlyMessageWhenThereIsNoArticle(): void
    {
        $client = self::createClient();
        self::getContainer()->set(ArticleRepository::class, new class implements ArticleRepository {
            public function findLatest(int $limit): array
            {
                return [];
            }
        });
        $client->request('GET', '/');

        self::assertSelectorExists('[data-testid="home-blog-empty"]');
        self::assertSelectorNotExists('[data-testid="home-blog-card"]');
    }

    public function testShowsTheErrorWhenArticlesCannotBeLoaded(): void
    {
        $client = self::createClient();
        self::getContainer()->set(ArticleRepository::class, new class implements ArticleRepository {
            public function findLatest(int $limit): array
            {
                throw new ArticlesLoadException();
            }
        });
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('[data-testid="home-blog-error"]', 'Impossible de charger les articles');
    }
}
