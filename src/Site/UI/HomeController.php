<?php

declare(strict_types=1);

namespace App\Site\UI;

use App\Blog\Domain\ArticleRepository;
use App\Blog\Domain\ArticlesLoadException;
use App\Site\Home\SlideRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    private const int LATEST_ARTICLES = 3;

    #[Route('/', name: 'home', methods: ['GET'])]
    public function __invoke(ArticleRepository $articleRepository, SlideRepository $slideRepository): Response
    {
        $slides = $slideRepository->findFeatured();

        try {
            return $this->render('home/index.html.twig', [
                'slides' => $slides,
                'articles' => $articleRepository->findLatest(self::LATEST_ARTICLES),
                'articles_error' => null,
            ]);
        } catch (ArticlesLoadException $exception) {
            return $this->render('home/index.html.twig', [
                'slides' => $slides,
                'articles' => [],
                'articles_error' => $exception->getMessage(),
            ]);
        }
    }
}
