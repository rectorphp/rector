<?php

declare(strict_types=1);

use PhpParser\NodeVisitor\NodeConnectingVisitor;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Configuration\Option;
use Rector\Core\FileSystem\FilesFinder;
use Rector\Core\Php\TypeAnalyzer;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeTypeResolver\DependencyInjection\PHPStanServicesFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PHPSTAN_FOR_RECTOR_PATH, getcwd() . '/phpstan-for-rector.neon');

    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->load('Rector\NodeTypeResolver\\', __DIR__ . '/../src')
        ->exclude([__DIR__ . '/../src/Contract']);

    $services->set(TypeAnalyzer::class);
    $services->set(FilesFinder::class);
    $services->set(BetterStandardPrinter::class);
    $services->set(BetterNodeFinder::class);

    $services->set(ReflectionProvider::class)
        ->factory([service(PHPStanServicesFactory::class), 'createReflectionProvider']);

    $services->set(NodeScopeResolver::class)
        ->factory([service(PHPStanServicesFactory::class), 'createNodeScopeResolver']);

    $services->set(ScopeFactory::class)
        ->factory([service(PHPStanServicesFactory::class), 'createScopeFactory']);

    $services->set(TypeNodeResolver::class)
        ->factory([service(PHPStanServicesFactory::class), 'createTypeNodeResolver']);

    $services->set(NodeConnectingVisitor::class);
};
