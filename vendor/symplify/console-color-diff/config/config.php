<?php

declare (strict_types=1);
namespace RectorPrefix20211125;

use RectorPrefix20211125\SebastianBergmann\Diff\Differ;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20211125\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('RectorPrefix20211125\Symplify\\ConsoleColorDiff\\', __DIR__ . '/../src');
    $services->set(\RectorPrefix20211125\SebastianBergmann\Diff\Differ::class);
    $services->set(\RectorPrefix20211125\Symplify\PackageBuilder\Reflection\PrivatesAccessor::class);
};
