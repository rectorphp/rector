<?php

declare(strict_types=1);

use Doctrine\Common\Annotations\Reader;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use Rector\BetterPhpDocParser\PhpDocParser\BetterPhpDocParser;
use Rector\DoctrineAnnotationGenerated\ConstantPreservingAnnotationReader;
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

    $services->set(Lexer::class);

    $services->alias(PhpDocParser::class, BetterPhpDocParser::class);

    $services->alias(Reader::class, ConstantPreservingAnnotationReader::class);
};
