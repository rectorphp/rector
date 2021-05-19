<?php

declare (strict_types=1);
namespace RectorPrefix20210519;

use PhpParser\ConstExprEvaluator;
use PhpParser\NodeFinder;
use RectorPrefix20210519\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20210519\Symplify\PackageBuilder\Php\TypeChecker;
return static function (\RectorPrefix20210519\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->autowire()->autoconfigure()->public();
    $services->load('RectorPrefix20210519\Symplify\\Astral\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/HttpKernel', __DIR__ . '/../src/StaticFactory', __DIR__ . '/../src/ValueObject']);
    $services->set(\PhpParser\ConstExprEvaluator::class);
    $services->set(\RectorPrefix20210519\Symplify\PackageBuilder\Php\TypeChecker::class);
    $services->set(\PhpParser\NodeFinder::class);
};
