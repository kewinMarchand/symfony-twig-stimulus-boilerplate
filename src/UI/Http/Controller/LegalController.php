<?php

declare(strict_types=1);

namespace App\UI\Http\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LegalController extends AbstractController
{
    #[Route('/mentions-legales', name: 'legal_notice', methods: ['GET'])]
    public function notice(): Response
    {
        return $this->render('legal/notice.html.twig');
    }

    #[Route('/donnees-personnelles', name: 'legal_privacy', methods: ['GET'])]
    public function privacy(): Response
    {
        return $this->render('legal/privacy.html.twig');
    }

    #[Route('/accessibilite', name: 'legal_accessibility', methods: ['GET'])]
    public function accessibility(): Response
    {
        return $this->render('legal/accessibility.html.twig');
    }

    #[Route('/plan-du-site', name: 'legal_sitemap', methods: ['GET'])]
    public function sitemap(): Response
    {
        return $this->render('legal/sitemap.html.twig');
    }
}
