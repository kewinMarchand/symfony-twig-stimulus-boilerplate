<?php

declare(strict_types=1);

namespace App\Site\Styleguide;

/**
 * Lit les tokens déclarés dans la feuille de style, pour que la charte n'en recopie aucune valeur.
 */
final class DesignTokens
{
    /**
     * @param string $libraryCss feuille de boilerplates-ui, qui résout les alias `var(--bui-…)` de la feuille du site
     *
     * @return array<string, string> nom du token => valeur, du premier bloc :root
     */
    public static function fromCss(string $css, string $prefix, string $libraryCss = ''): array
    {
        $library = self::rootDeclarations($libraryCss, '');
        $tokens = [];
        foreach (self::rootDeclarations($css, $prefix) as $name => $value) {
            $alias = 1 === preg_match('/^var\((--[\w-]+)\)$/', $value, $match) ? $match[1] : null;
            $tokens[$name] = null !== $alias ? ($library[$alias] ?? $value) : $value;
        }

        return $tokens;
    }

    /**
     * @return array<string, string>
     */
    private static function rootDeclarations(string $css, string $prefix): array
    {
        if (1 !== preg_match('/:root\s*\{(.*?)\}/s', $css, $block)) {
            return [];
        }

        preg_match_all('/(--'.preg_quote($prefix, '/').'[\w-]*)\s*:\s*([^;]+);/', $block[1], $matches, \PREG_SET_ORDER);

        $declarations = [];
        foreach ($matches as $match) {
            $declarations[$match[1]] = trim($match[2]);
        }

        return $declarations;
    }
}
