<?php

declare(strict_types=1);

namespace App\Shared\UI\Twig;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Attribute\AsTwigFunction;

/**
 * Expose aux templates la configuration d'observabilité, seulement quand elle est renseignée :
 * sans valeur, les templates n'injectent aucun script et le navigateur n'envoie rien.
 */
final readonly class ObservabilityExtension
{
    public function __construct(
        #[Autowire('%env(GLITCHTIP_DSN)%')]
        private string $glitchtipDsn,
        #[Autowire('%env(default:kernel.environment:GLITCHTIP_ENVIRONMENT)%')]
        private string $glitchtipEnvironment,
    ) {
    }

    /**
     * @return array{dsn: string, environment: string}|null
     */
    #[AsTwigFunction('glitchtip')]
    public function glitchtip(): ?array
    {
        $dsn = trim($this->glitchtipDsn);

        return '' === $dsn ? null : ['dsn' => $dsn, 'environment' => $this->glitchtipEnvironment];
    }
}
