<?php

declare(strict_types=1);

namespace App\Site\Home;

interface SlideRepository
{
    /**
     * @return list<Slide>
     */
    public function findFeatured(): array;
}
