<?php

declare(strict_types=1);

namespace App\Site\UI;

use App\Shared\UI\IconNames;
use App\Site\Styleguide\ContrastRatio;
use App\Site\Styleguide\DesignTokens;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;

final class StyleguideController extends AbstractController
{
    public function __invoke(
        #[Autowire('%kernel.project_dir%/assets/styles/app.css')] string $stylesheet,
        #[Autowire('%kernel.project_dir%/assets/boilerplates-ui/boilerplates-ui.css')] string $libraryStylesheet,
    ): Response {
        $css = (string) file_get_contents($stylesheet);
        $library = (string) file_get_contents($libraryStylesheet);
        $colors = DesignTokens::fromCss($css, 'color-', $library);
        $background = $colors['--color-background'] ?? '#ffffff';

        return $this->render('styleguide/index.html.twig', [
            'colors' => array_map(static fn (string $value): array => [
                'value' => $value,
                'contrast' => 1 === preg_match('/^#[0-9a-f]{6}$/i', $value) ? ContrastRatio::between($value, $background) : null,
            ], $colors),
            'spaces' => DesignTokens::fromCss($css, 'space-', $library),
            'font_sizes' => DesignTokens::fromCss($css, 'font-size-', $library),
            'radius' => DesignTokens::fromCss($css, 'radius', $library),
            'icons' => IconNames::ALL,
        ]);
    }
}
