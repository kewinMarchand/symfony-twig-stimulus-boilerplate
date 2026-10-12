<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\InMemory;

use App\Catalog\Domain\CatalogQuery;
use App\Catalog\Domain\CatalogRepository;
use App\Catalog\Domain\CatalogResult;
use App\Catalog\Domain\Category;
use App\Catalog\Domain\CategoryTree;
use App\Catalog\Domain\Exposure;
use App\Catalog\Domain\Price;
use App\Catalog\Domain\Product;
use App\Catalog\Domain\Size;

final readonly class InMemoryCatalogRepository implements CatalogRepository
{
    private const int LATENCY_MS = 300;

    public function categoryTree(): CategoryTree
    {
        return new CategoryTree(new Category('', 'Catalogue', [
            new Category('plantes-interieur', 'Plantes d’intérieur', [
                new Category('feuillages', 'Feuillages', [
                    new Category('monstera', 'Monstera'),
                    new Category('fougeres', 'Fougères'),
                ]),
                new Category('plantes-a-fleurs', 'Plantes à fleurs', [
                    new Category('anthurium', 'Anthurium'),
                    new Category('strelitzia', 'Strelitzia'),
                ]),
            ]),
            new Category('plantes-exterieur', 'Plantes d’extérieur', [
                new Category('palmiers', 'Palmiers'),
                new Category('arbustes-a-fleurs', 'Arbustes à fleurs', [
                    new Category('heliconia', 'Heliconia'),
                    new Category('calliandra', 'Calliandra'),
                ]),
            ]),
            new Category('plantes-aquatiques', 'Plantes aquatiques', [
                new Category('nenuphars', 'Nénuphars'),
            ]),
        ]));
    }

    public function search(Category $category, CatalogQuery $query): CatalogResult
    {
        usleep(self::LATENCY_MS * 1000);

        return CatalogSearch::search($this->products(), $category, $query);
    }

    /**
     * @return list<Product>
     */
    private function products(): array
    {
        return [
            new Product('1', 'monstera-deliciosa', 'Monstera deliciosa', 'monstera', Price::fromCents(2990), Exposure::PartialShade, Size::Medium, true, 6),
            new Product('2', 'monstera-adansonii', 'Monstera adansonii', 'monstera', Price::fromCents(1990), Exposure::PartialShade, Size::Small, true, 6),
            new Product('3', 'monstera-thai-constellation', 'Monstera Thai Constellation', 'monstera', Price::fromCents(8900), Exposure::PartialShade, Size::Large, false, 6),
            new Product('4', 'fougere-de-boston', 'Fougère de Boston', 'fougeres', Price::fromCents(1490), Exposure::Shade, Size::Small, true, 7),
            new Product('5', 'fougere-arborescente', 'Fougère arborescente', 'fougeres', Price::fromCents(7900), Exposure::Shade, Size::Large, true, 8),
            new Product('6', 'capillaire', 'Capillaire', 'fougeres', Price::fromCents(1290), Exposure::Shade, Size::Small, false, 7),
            new Product('7', 'anthurium-rouge', 'Anthurium rouge', 'anthurium', Price::fromCents(2490), Exposure::PartialShade, Size::Medium, true, 2),
            new Product('8', 'anthurium-blanc', 'Anthurium blanc', 'anthurium', Price::fromCents(2690), Exposure::PartialShade, Size::Medium, true, 2),
            new Product('9', 'anthurium-clarinervium', 'Anthurium clarinervium', 'anthurium', Price::fromCents(4590), Exposure::Shade, Size::Small, false, 2),
            new Product('10', 'strelitzia-reginae', 'Strelitzia reginae', 'strelitzia', Price::fromCents(3990), Exposure::Sun, Size::Medium, true, 4),
            new Product('11', 'strelitzia-nicolai', 'Strelitzia nicolai', 'strelitzia', Price::fromCents(6990), Exposure::Sun, Size::Large, true, 4),
            new Product('12', 'palmier-areca', 'Palmier areca', 'palmiers', Price::fromCents(3490), Exposure::PartialShade, Size::Medium, true, 9),
            new Product('13', 'palmier-kentia', 'Palmier kentia', 'palmiers', Price::fromCents(5990), Exposure::PartialShade, Size::Large, true, 10),
            new Product('14', 'palmier-royal', 'Palmier royal', 'palmiers', Price::fromCents(12900), Exposure::Sun, Size::Large, false, 11),
            new Product('15', 'palmier-bambou', 'Palmier bambou', 'palmiers', Price::fromCents(2990), Exposure::PartialShade, Size::Medium, true, 12),
            new Product('16', 'heliconia-rostrata', 'Heliconia rostrata', 'heliconia', Price::fromCents(3290), Exposure::Sun, Size::Large, true, 1),
            new Product('17', 'heliconia-psittacorum', 'Heliconia psittacorum', 'heliconia', Price::fromCents(2290), Exposure::Sun, Size::Medium, true, 1),
            new Product('18', 'heliconia-wagneriana', 'Heliconia wagneriana', 'heliconia', Price::fromCents(3890), Exposure::PartialShade, Size::Large, false, 1),
            new Product('19', 'calliandra-haematocephala', 'Calliandra haematocephala', 'calliandra', Price::fromCents(2790), Exposure::Sun, Size::Medium, true, 3),
            new Product('20', 'calliandra-surinamensis', 'Calliandra surinamensis', 'calliandra', Price::fromCents(2490), Exposure::Sun, Size::Medium, true, 3),
            new Product('21', 'calliandra-naine', 'Calliandra naine', 'calliandra', Price::fromCents(1790), Exposure::Sun, Size::Small, true, 3),
            new Product('22', 'nenuphar-bleu', 'Nénuphar bleu d’Égypte', 'nenuphars', Price::fromCents(1990), Exposure::Sun, Size::Small, true, 5),
            new Product('23', 'nenuphar-violet', 'Nénuphar tropical violet', 'nenuphars', Price::fromCents(2390), Exposure::Sun, Size::Medium, true, 5),
            new Product('24', 'nenuphar-geant', 'Nénuphar géant', 'nenuphars', Price::fromCents(9900), Exposure::Sun, Size::Large, false, 5),
        ];
    }
}
