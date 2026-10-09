<?php

declare(strict_types=1);

namespace App\Domain\Tasks\Model;

use App\Domain\Tasks\Exception\InvalidTaskTitleException;

final readonly class Task
{
    public string $title;

    public function __construct(
        public string $id,
        string $title,
        public bool $done = false,
    ) {
        $title = trim($title);

        if ('' === $title) {
            throw new InvalidTaskTitleException();
        }

        $this->title = $title;
    }
}
