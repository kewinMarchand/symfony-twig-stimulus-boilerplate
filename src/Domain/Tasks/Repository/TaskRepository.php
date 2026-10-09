<?php

declare(strict_types=1);

namespace App\Domain\Tasks\Repository;

use App\Domain\Tasks\Exception\TasksLoadException;
use App\Domain\Tasks\Model\Task;

interface TaskRepository
{
    /**
     * @return list<Task>
     *
     * @throws TasksLoadException
     */
    public function findAll(): array;
}
