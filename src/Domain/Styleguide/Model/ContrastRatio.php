<?php

declare(strict_types=1);

namespace App\Domain\Styleguide\Model;

final readonly class ContrastRatio
{
    private const float AA = 4.5;
    private const float AAA = 7.0;

    private function __construct(public float $value)
    {
    }

    public static function between(string $foreground, string $background): self
    {
        $lighter = max(self::luminance($foreground), self::luminance($background));
        $darker = min(self::luminance($foreground), self::luminance($background));

        return new self(round(($lighter + 0.05) / ($darker + 0.05), 2));
    }

    public function level(): string
    {
        return match (true) {
            $this->value >= self::AAA => 'AAA',
            $this->value >= self::AA => 'AA',
            default => 'insuffisant',
        };
    }

    private static function luminance(string $hex): float
    {
        if (1 !== preg_match('/^#([0-9a-f]{6})$/i', $hex, $matches)) {
            throw new \InvalidArgumentException(\sprintf('Couleur hexadécimale attendue sur 6 chiffres, « %s » reçu.', $hex));
        }

        [$red, $green, $blue] = array_map(static function (string $channel): float {
            $value = hexdec($channel) / 255;

            return $value <= 0.03928 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }, str_split($matches[1], 2));

        return 0.2126 * $red + 0.7152 * $green + 0.0722 * $blue;
    }
}
