<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Domain\Tasks\Exception\TasksLoadException;
use App\Domain\Tasks\Model\Task;
use App\Domain\Tasks\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TasksControllerTest extends WebTestCase
{
    public function testPageRendersTheLoadingStateInALazyFrame(): void
    {
        $client = self::createClient();
        $client->request('GET', '/taches');

        self::assertSelectorExists('turbo-frame#tasks[loading="lazy"][src="/taches/liste"]');
        self::assertSelectorExists('[data-testid="tasks-loading"][role="status"]');
    }

    public function testFrameRequestGetsOnlyTheFrame(): void
    {
        $client = self::createClient();
        $client->request('GET', '/taches/liste', server: ['HTTP_TURBO_FRAME' => 'tasks']);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('turbo-frame#tasks [data-testid="tasks-list"]');
        self::assertSelectorNotExists('h1');
    }

    public function testRequestWithoutJavaScriptGetsAFullNoindexPage(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/taches/liste');

        self::assertSelectorTextSame('h1', 'Tâches');
        self::assertSelectorCount(3, '[data-testid="tasks-item"]');
        self::assertSame('noindex, follow', $crawler->filter('meta[name="robots"]')->attr('content'));
        self::assertSame('http://localhost/taches', $crawler->filter('link[rel="canonical"]')->attr('href'));
    }

    public function testFrameListsTasks(): void
    {
        $client = self::createClient();
        $this->useRepository(new class implements TaskRepository {
            public function findAll(): array
            {
                return [new Task('1', 'Écrire les tests'), new Task('2', 'Livrer', true)];
            }
        });
        $client->request('GET', '/taches/liste');

        self::assertResponseIsSuccessful();
        self::assertSelectorCount(2, '[data-testid="tasks-item"]');
        self::assertSelectorTextContains('[data-testid="tasks-list"]', 'Terminée');
    }

    public function testFrameShowsAFriendlyEmptyState(): void
    {
        $client = self::createClient();
        $this->useRepository(new class implements TaskRepository {
            public function findAll(): array
            {
                return [];
            }
        });
        $client->request('GET', '/taches/liste');

        self::assertSelectorExists('[data-testid="tasks-empty"]');
        self::assertSelectorNotExists('[data-testid="tasks-list"]');
    }

    public function testFrameShowsTheErrorWithARetryButton(): void
    {
        $client = self::createClient();
        $this->useRepository(new class implements TaskRepository {
            public function findAll(): array
            {
                throw new TasksLoadException();
            }
        });
        $client->request('GET', '/taches/liste');

        self::assertResponseStatusCodeSame(503);
        self::assertSelectorTextContains('[data-testid="tasks-error"]', 'Impossible de charger les tâches');
        self::assertSelectorExists('[data-testid="tasks-retry"]');
    }

    private function useRepository(TaskRepository $repository): void
    {
        self::getContainer()->set(TaskRepository::class, $repository);
    }
}
