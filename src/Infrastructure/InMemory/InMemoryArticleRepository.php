<?php

declare(strict_types=1);

namespace App\Infrastructure\InMemory;

use App\Domain\Home\Model\Article;
use App\Domain\Home\Repository\ArticleRepository;

final readonly class InMemoryArticleRepository implements ArticleRepository
{
    private const int LATENCY_MS = 300;

    public function findLatest(int $limit): array
    {
        usleep(self::LATENCY_MS * 1000);

        $articles = [
            new Article(
                '1',
                'Une couche de routage sans logique',
                'Chaque contrôleur délègue à une vue Twig rangée par domaine, et le domaine ne dépend d’aucun framework.',
                new \DateTimeImmutable('2026-09-24'),
            ),
            new Article(
                '2',
                'Turbo Frame et ses trois états',
                'La liste des tâches se charge à la demande, avec un squelette, un message d’erreur relançable et un état vide.',
                new \DateTimeImmutable('2026-09-10'),
            ),
            new Article(
                '3',
                'Un formulaire qui marche sans JavaScript',
                'Symfony Form valide côté serveur, relie chaque erreur à son champ, et Turbo n’est qu’une amélioration.',
                new \DateTimeImmutable('2026-08-27'),
            ),
        ];

        return \array_slice($articles, 0, $limit);
    }
}
