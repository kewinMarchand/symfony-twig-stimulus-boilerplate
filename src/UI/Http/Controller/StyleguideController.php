<?php

declare(strict_types=1);

namespace App\UI\Http\Controller;

use App\Domain\Styleguide\Model\ContrastRatio;
use App\UI\Styleguide\DesignTokens;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;

final class StyleguideController extends AbstractController
{
    public function __invoke(#[Autowire('%kernel.project_dir%/assets/styles/app.css')] string $stylesheet): Response
    {
        $css = (string) file_get_contents($stylesheet);
        $colors = DesignTokens::fromCss($css, 'color-');
        $background = $colors['--color-background'] ?? '#ffffff';

        return $this->render('styleguide/index.html.twig', [
            'colors' => array_map(static fn (string $value): array => [
                'value' => $value,
                'contrast' => 1 === preg_match('/^#[0-9a-f]{6}$/i', $value) ? ContrastRatio::between($value, $background) : null,
            ], $colors),
            'spaces' => DesignTokens::fromCss($css, 'space-'),
            'font_sizes' => DesignTokens::fromCss($css, 'font-size-'),
            'radius' => DesignTokens::fromCss($css, 'radius'),
            'icons' => array_map(static fn (string $file): string => basename($file, '.svg'), glob(\dirname($stylesheet, 2).'/icons/lucide/*.svg') ?: []),
        ]);
    }
}
