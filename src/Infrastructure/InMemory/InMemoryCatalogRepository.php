<?php

declare(strict_types=1);

namespace App\Infrastructure\InMemory;

use App\Domain\Catalog\Model\CatalogQuery;
use App\Domain\Catalog\Model\CatalogResult;
use App\Domain\Catalog\Model\Category;
use App\Domain\Catalog\Model\CategoryTree;
use App\Domain\Catalog\Model\Exposure;
use App\Domain\Catalog\Model\Product;
use App\Domain\Catalog\Model\Size;
use App\Domain\Catalog\Repository\CatalogRepository;
use App\Domain\Catalog\Service\CatalogSearch;

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
            new Product('1', 'monstera-deliciosa', 'Monstera deliciosa', 'monstera', 2990, Exposure::PartialShade, Size::Medium, true, 6),
            new Product('2', 'monstera-adansonii', 'Monstera adansonii', 'monstera', 1990, Exposure::PartialShade, Size::Small, true, 6),
            new Product('3', 'monstera-thai-constellation', 'Monstera Thai Constellation', 'monstera', 8900, Exposure::PartialShade, Size::Large, false, 6),
            new Product('4', 'fougere-de-boston', 'Fougère de Boston', 'fougeres', 1490, Exposure::Shade, Size::Small, true, 7),
            new Product('5', 'fougere-arborescente', 'Fougère arborescente', 'fougeres', 7900, Exposure::Shade, Size::Large, true, 8),
            new Product('6', 'capillaire', 'Capillaire', 'fougeres', 1290, Exposure::Shade, Size::Small, false, 7),
            new Product('7', 'anthurium-rouge', 'Anthurium rouge', 'anthurium', 2490, Exposure::PartialShade, Size::Medium, true, 2),
            new Product('8', 'anthurium-blanc', 'Anthurium blanc', 'anthurium', 2690, Exposure::PartialShade, Size::Medium, true, 2),
            new Product('9', 'anthurium-clarinervium', 'Anthurium clarinervium', 'anthurium', 4590, Exposure::Shade, Size::Small, false, 2),
            new Product('10', 'strelitzia-reginae', 'Strelitzia reginae', 'strelitzia', 3990, Exposure::Sun, Size::Medium, true, 4),
            new Product('11', 'strelitzia-nicolai', 'Strelitzia nicolai', 'strelitzia', 6990, Exposure::Sun, Size::Large, true, 4),
            new Product('12', 'palmier-areca', 'Palmier areca', 'palmiers', 3490, Exposure::PartialShade, Size::Medium, true, 9),
            new Product('13', 'palmier-kentia', 'Palmier kentia', 'palmiers', 5990, Exposure::PartialShade, Size::Large, true, 10),
            new Product('14', 'palmier-royal', 'Palmier royal', 'palmiers', 12900, Exposure::Sun, Size::Large, false, 11),
            new Product('15', 'palmier-bambou', 'Palmier bambou', 'palmiers', 2990, Exposure::PartialShade, Size::Medium, true, 12),
            new Product('16', 'heliconia-rostrata', 'Heliconia rostrata', 'heliconia', 3290, Exposure::Sun, Size::Large, true, 1),
            new Product('17', 'heliconia-psittacorum', 'Heliconia psittacorum', 'heliconia', 2290, Exposure::Sun, Size::Medium, true, 1),
            new Product('18', 'heliconia-wagneriana', 'Heliconia wagneriana', 'heliconia', 3890, Exposure::PartialShade, Size::Large, false, 1),
            new Product('19', 'calliandra-haematocephala', 'Calliandra haematocephala', 'calliandra', 2790, Exposure::Sun, Size::Medium, true, 3),
            new Product('20', 'calliandra-surinamensis', 'Calliandra surinamensis', 'calliandra', 2490, Exposure::Sun, Size::Medium, true, 3),
            new Product('21', 'calliandra-naine', 'Calliandra naine', 'calliandra', 1790, Exposure::Sun, Size::Small, true, 3),
            new Product('22', 'nenuphar-bleu', 'Nénuphar bleu d’Égypte', 'nenuphars', 1990, Exposure::Sun, Size::Small, true, 5),
            new Product('23', 'nenuphar-violet', 'Nénuphar tropical violet', 'nenuphars', 2390, Exposure::Sun, Size::Medium, true, 5),
            new Product('24', 'nenuphar-geant', 'Nénuphar géant', 'nenuphars', 9900, Exposure::Sun, Size::Large, false, 5),
        ];
    }
}
