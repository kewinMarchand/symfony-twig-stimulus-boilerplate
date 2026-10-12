<?php

declare(strict_types=1);

namespace App\Task\Domain;

final class TasksLoadException extends \RuntimeException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct('Impossible de charger les tâches pour le moment.', 0, $previous);
    }
}
