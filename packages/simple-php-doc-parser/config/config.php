<?php

declare(strict_types=1);

use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use Rector\SimplePhpDocParser\SimplePhpDocParser;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->load('Rector\SimplePhpDocParser\\', __DIR__ . '/../src');

    $services->set(SimplePhpDocParser::class);
    $services->set(PhpDocParser::class);
    $services->set(Lexer::class);
};
