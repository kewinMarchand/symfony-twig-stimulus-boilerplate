<?php

declare(strict_types=1);

namespace App\UI\Http\Controller;

use App\Domain\Tasks\Exception\TasksLoadException;
use App\Domain\Tasks\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TasksController extends AbstractController
{
    #[Route('/taches', name: 'tasks', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('tasks/index.html.twig');
    }

    /**
     * Contenu du Turbo Frame, ou page complète quand le navigateur n'exécute pas JavaScript.
     */
    #[Route('/taches/liste', name: 'tasks_list', methods: ['GET'])]
    public function list(Request $request, TaskRepository $taskRepository): Response
    {
        $template = $request->headers->has('Turbo-Frame') ? 'tasks/_list.html.twig' : 'tasks/index.html.twig';

        try {
            return $this->render($template, [
                'tasks' => $taskRepository->findAll(),
                'error' => null,
            ]);
        } catch (TasksLoadException $exception) {
            return $this->render($template, [
                'tasks' => [],
                'error' => $exception->getMessage(),
            ], new Response(status: Response::HTTP_SERVICE_UNAVAILABLE));
        }
    }
}
