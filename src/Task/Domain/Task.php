<?php

declare(strict_types=1);

namespace App\Task\Domain;

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
