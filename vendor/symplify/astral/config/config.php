<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use PhpParser\ConstExprEvaluator;
use PhpParser\NodeFinder;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20210510\Symplify\PackageBuilder\Php\TypeChecker;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->autowire()->autoconfigure()->public();
    $services->load('RectorPrefix20210510\Symplify\\Astral\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/HttpKernel', __DIR__ . '/../src/StaticFactory', __DIR__ . '/../src/ValueObject']);
    $services->set(ConstExprEvaluator::class);
    $services->set(TypeChecker::class);
    $services->set(NodeFinder::class);
};
