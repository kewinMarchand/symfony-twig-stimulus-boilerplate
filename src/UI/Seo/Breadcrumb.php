<?php

declare(strict_types=1);

namespace App\UI\Seo;

final readonly class Breadcrumb
{
    /**
     * @param list<BreadcrumbItem> $items
     */
    private function __construct(public array $items)
    {
    }

    /**
     * Fil d'Ariane à partir des étapes qui suivent l'accueil. Vide pour l'accueil lui-même,
     * le dernier élément n'a jamais de lien.
     *
     * @param list<array{label: string, path?: string}> $trail
     */
    public static function fromTrail(array $trail): self
    {
        if ([] === $trail) {
            return new self([]);
        }

        $items = [new BreadcrumbItem('Accueil', '/')];
        $last = array_key_last($trail);
        foreach ($trail as $index => $step) {
            $items[] = new BreadcrumbItem($step['label'], $index === $last ? null : ($step['path'] ?? null));
        }

        return new self($items);
    }

    public function isEmpty(): bool
    {
        return [] === $this->items;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function jsonLd(string $siteUrl, string $currentUrl): ?array
    {
        if ($this->isEmpty()) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(static fn (int $index, BreadcrumbItem $item): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item->label,
                'item' => null === $item->path ? $currentUrl : $siteUrl.$item->path,
            ], array_keys($this->items), $this->items),
        ];
    }
}
