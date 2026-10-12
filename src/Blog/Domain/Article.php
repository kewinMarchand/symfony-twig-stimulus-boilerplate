<?php

declare(strict_types=1);

namespace App\Blog\Domain;

final readonly class Article
{
    public function __construct(
        public string $id,
        public string $title,
        public string $excerpt,
        public \DateTimeImmutable $publishedAt,
    ) {
    }
}
