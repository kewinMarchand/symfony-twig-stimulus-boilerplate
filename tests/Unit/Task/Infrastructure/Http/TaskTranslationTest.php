<?php

declare(strict_types=1);

namespace App\Tests\Unit\Task\Infrastructure\Http;

use App\Shared\Infrastructure\Http\InvalidApiPayloadException;
use App\Task\Infrastructure\Http\TaskTranslation;
use PHPUnit\Framework\TestCase;

final class TaskTranslationTest extends TestCase
{
    public function testTranslatesTheApiTasks(): void
    {
        $tasks = TaskTranslation::tasks(self::payload('tasks'));

        self::assertCount(3, $tasks);
        self::assertSame('1', $tasks[0]->id);
        self::assertSame('Brancher la vraie API', $tasks[0]->title);
        self::assertTrue($tasks[2]->done);
    }

    public function testRejectsAnUnexpectedShape(): void
    {
        $this->expectException(InvalidApiPayloadException::class);

        TaskTranslation::tasks([['id' => 1, 'title' => 'Sans statut']]);
    }

    private static function payload(string $name): mixed
    {
        return json_decode((string) file_get_contents(__DIR__.'/../../../../Fixtures/api/'.$name.'.json'), true, flags: \JSON_THROW_ON_ERROR);
    }
}
