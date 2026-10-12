<?php

declare(strict_types=1);

namespace App\Site\Home;

final readonly class Slide
{
    public function __construct(
        public string $image,
        public string $title,
        public string $text,
    ) {
    }
}
