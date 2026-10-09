<?php

declare(strict_types=1);

namespace App\Domain\Tasks\Exception;

final class InvalidTaskTitleException extends \InvalidArgumentException
{
    public function __construct()
    {
        parent::__construct('Le titre d’une tâche ne peut pas être vide.');
    }
}
