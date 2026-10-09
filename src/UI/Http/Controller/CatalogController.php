<?php

declare(strict_types=1);

namespace App\UI\Http\Controller;

use App\Domain\Catalog\Exception\CatalogLoadException;
use App\Domain\Catalog\Repository\CatalogRepository;
use App\UI\Http\Catalog\CatalogUrlState;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CatalogController extends AbstractController
{
    #[Route('/catalogue/{path}', name: 'catalog', requirements: ['path' => '[a-z0-9-]+(?:/[a-z0-9-]+){0,2}'], defaults: ['path' => ''], methods: ['GET'])]
    public function __invoke(Request $request, CatalogRepository $catalogRepository, string $path): Response
    {
        $tree = $catalogRepository->categoryTree();
        $trail = $tree->trail('' === $path ? [] : explode('/', $path));
        if (null === $trail) {
            throw $this->createNotFoundException();
        }

        $rawQuery = $request->server->getString('QUERY_STRING');
        $state = CatalogUrlState::fromQueryString($rawQuery);
        $canonicalQuery = $state->queryString();
        if ('' !== $rawQuery && '?'.$rawQuery !== $canonicalQuery) {
            return $this->redirect($this->generateUrl('catalog', ['path' => $path]).$canonicalQuery, Response::HTTP_MOVED_PERMANENTLY);
        }

        $category = [] === $trail ? $tree->root : $trail[array_key_last($trail)];

        try {
            $result = $catalogRepository->search($category, $state->query);
        } catch (CatalogLoadException $exception) {
            return $this->render('catalog/index.html.twig', [
                'path' => $path,
                'trail' => $trail,
                'category' => $category,
                'state' => $state,
                'result' => null,
                'error' => $exception->getMessage(),
            ], new Response(status: Response::HTTP_SERVICE_UNAVAILABLE));
        }

        if ($state->query->page > $result->page->pageCount) {
            throw $this->createNotFoundException();
        }

        return $this->render('catalog/index.html.twig', [
            'path' => $path,
            'trail' => $trail,
            'category' => $category,
            'state' => $state,
            'result' => $result,
            'error' => null,
        ]);
    }
}
