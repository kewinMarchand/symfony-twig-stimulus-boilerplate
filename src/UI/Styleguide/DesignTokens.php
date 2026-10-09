<?php

declare(strict_types=1);

namespace App\UI\Styleguide;

/**
 * Lit les tokens déclarés dans la feuille de style, pour que la charte n'en recopie aucune valeur.
 */
final class DesignTokens
{
    /**
     * @return array<string, string> nom du token => valeur, du premier bloc :root
     */
    public static function fromCss(string $css, string $prefix): array
    {
        if (1 !== preg_match('/:root\s*\{(.*?)\}/s', $css, $block)) {
            return [];
        }

        preg_match_all('/(--'.preg_quote($prefix, '/').'[\w-]*)\s*:\s*([^;]+);/', $block[1], $matches, \PREG_SET_ORDER);

        $tokens = [];
        foreach ($matches as $match) {
            $tokens[$match[1]] = trim($match[2]);
        }

        return $tokens;
    }
}
