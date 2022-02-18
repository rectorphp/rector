<?php

declare (strict_types=1);
namespace RectorPrefix20220218;

use RectorPrefix20220218\SebastianBergmann\Diff\Differ;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20220218\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('RectorPrefix20220218\Symplify\\ConsoleColorDiff\\', __DIR__ . '/../src');
    $services->set(\RectorPrefix20220218\SebastianBergmann\Diff\Differ::class);
    $services->set(\RectorPrefix20220218\Symplify\PackageBuilder\Reflection\PrivatesAccessor::class);
};
