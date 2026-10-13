<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\Http;

use App\Shared\Infrastructure\Http\ApiPayload;
use App\Task\Domain\Task;

final class TaskTranslation
{
    /**
     * @return list<Task>
     */
    public static function tasks(mixed $payload): array
    {
        return array_map(
            static fn (array $task): Task => new Task(ApiPayload::id($task, 'id'), ApiPayload::string($task, 'title'), ApiPayload::bool($task, 'done')),
            ApiPayload::objects($payload, 'tasks'),
        );
    }
}
