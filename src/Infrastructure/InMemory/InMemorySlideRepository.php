<?php

declare(strict_types=1);

namespace App\Infrastructure\InMemory;

use App\Domain\Home\Model\Slide;
use App\Domain\Home\Repository\SlideRepository;

final readonly class InMemorySlideRepository implements SlideRepository
{
    public function findFeatured(): array
    {
        return [
            new Slide('slide-1', 'Héliconia', 'Ses bractées rouges et jaunes en zigzag attirent les colibris.'),
            new Slide('slide-2', 'Anthurium', 'Une spathe vernissée qui dure des semaines sur la plante.'),
            new Slide('slide-3', 'Calliandra', 'Des pompons d’étamines rouges, légers comme une houppette.'),
            new Slide('slide-4', 'Strelitzia', 'L’oiseau de paradis, crête orange et bec bleu.'),
            new Slide('slide-5', 'Nénuphar', 'Une fleur posée à fleur d’eau, ouverte au soleil.'),
        ];
    }
}
