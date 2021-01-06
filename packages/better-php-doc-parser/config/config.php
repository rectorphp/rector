<?php

declare(strict_types=1);

use Doctrine\Common\Annotations\Reader;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use Rector\DoctrineAnnotationGenerated\ConstantPreservingAnnotationReader;
use Rector\PhpdocParserPrinter\Parser\TokenAwarePhpDocParser;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->public()
        ->autoconfigure();

    $services->load('Rector\BetterPhpDocParser\\', __DIR__ . '/../src')
        ->exclude([
            __DIR__ . '/../src/ValueObject',
            __DIR__ . '/../src/*/*Info.php',
            __DIR__ . '/../src/*Info.php',
            __DIR__ . '/../src/Attributes/Ast/PhpDoc',
            __DIR__ . '/../src/PhpDocNode',
        ]);

    // @todo add token aware
    $services->alias(PhpDocParser::class, TokenAwarePhpDocParser::class);

    $services->alias(Reader::class, ConstantPreservingAnnotationReader::class);
};
