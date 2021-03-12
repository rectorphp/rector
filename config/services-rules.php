<?php

declare(strict_types=1);

use Rector\PSR4\Composer\PSR4NamespaceMatcher;
use Rector\PSR4\Contract\PSR4AutoloadNamespaceMatcherInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Yaml\Parser;
use Symplify\PhpConfigPrinter\Printer\PhpParserPhpConfigPrinter;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // psr-4
    $services->alias(PSR4AutoloadNamespaceMatcherInterface::class, PSR4NamespaceMatcher::class);

    // symfony code-quality
    $services->set(Parser::class);
    $services->set(PhpParserPhpConfigPrinter::class);
};
