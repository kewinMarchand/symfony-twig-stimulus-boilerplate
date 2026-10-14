<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

echo App\Shared\Infrastructure\Security\SiteCsp::startupLine(getenv('APP_ENV') ?: 'prod'), \PHP_EOL;
