<?php

declare(strict_types=1);

namespace App\Infrastructure\InMemory;

use App\Domain\Tasks\Model\Task;
use App\Domain\Tasks\Repository\TaskRepository;

final readonly class InMemoryTaskRepository implements TaskRepository
{
    private const int LATENCY_MS = 300;

    public function findAll(): array
    {
        usleep(self::LATENCY_MS * 1000);

        return [
            new Task('1', 'Brancher la vraie API'),
            new Task('2', 'Écrire l’adaptateur HttpClient'),
            new Task('3', 'Lancer make qa', done: true),
        ];
    }
}
