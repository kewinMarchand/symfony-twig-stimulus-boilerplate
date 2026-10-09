<?php

declare(strict_types=1);

namespace App\Domain\Home\Repository;

use App\Domain\Home\Model\Slide;

interface SlideRepository
{
    /**
     * @return list<Slide>
     */
    public function findFeatured(): array;
}
