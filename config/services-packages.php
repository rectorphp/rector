<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\PSR4\Composer\PSR4NamespaceMatcher;
use Rector\PSR4\Contract\PSR4AutoloadNamespaceMatcherInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Yaml\Parser;
use Symplify\PhpConfigPrinter\Printer\PhpParserPhpConfigPrinter;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->load('Rector\\', __DIR__ . '/../packages')
        ->exclude([
            // @todo move to value object
            __DIR__ . '/../packages/*/{ValueObject,Contract,Exception}',
            __DIR__ . '/../packages/AttributeAwarePhpDoc/Ast',
            __DIR__ . '/../packages/BetterPhpDocParser/Attributes/Ast/PhpDoc',
            __DIR__ . '/../packages/BetterPhpDocParser/Attributes/Attribute',
            __DIR__ . '/../packages/BetterPhpDocParser/PhpDocInfo/PhpDocInfo.php',
            __DIR__ . '/../packages/Testing/PHPUnit',

            // used in PHPStan
            __DIR__ . '/../packages/NodeTypeResolver/Reflection/BetterReflection/RectorBetterReflectionSourceLocatorFactory.php',
            __DIR__ . '/../packages/NodeTypeResolver/Reflection/BetterReflection/SourceLocatorProvider/DynamicSourceLocatorProvider.php',
        ]);
};
