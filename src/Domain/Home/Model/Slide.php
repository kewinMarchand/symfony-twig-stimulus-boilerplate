<?php

declare(strict_types=1);

namespace App\Domain\Home\Model;

final readonly class Slide
{
    public function __construct(
        public string $image,
        public string $title,
        public string $text,
    ) {
    }
}
