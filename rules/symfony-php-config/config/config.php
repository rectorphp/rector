<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Yaml\Parser;
use Symplify\PhpConfigPrinter\Printer\PhpParserPhpConfigPrinter;
use Symplify\PhpConfigPrinter\YamlToPhpConverter;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->load('Rector\SymfonyPhpConfig\\', __DIR__ . '/../src')
        ->exclude([__DIR__ . '/../src/Rector']);

    $services->set(YamlToPhpConverter::class);
    $services->set(Parser::class);
    $services->set(PhpParserPhpConfigPrinter::class);
};
