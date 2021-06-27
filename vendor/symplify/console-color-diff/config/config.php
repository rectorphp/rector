<?php

declare (strict_types=1);
namespace RectorPrefix20210627;

use RectorPrefix20210627\SebastianBergmann\Diff\Differ;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20210627\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('RectorPrefix20210627\Symplify\\ConsoleColorDiff\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/Bundle']);
    $services->set(\RectorPrefix20210627\SebastianBergmann\Diff\Differ::class);
    $services->set(\RectorPrefix20210627\Symplify\PackageBuilder\Reflection\PrivatesAccessor::class);
};
