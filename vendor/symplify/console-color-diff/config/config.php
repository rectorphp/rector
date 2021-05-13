<?php

declare (strict_types=1);
namespace RectorPrefix20210513;

use RectorPrefix20210513\SebastianBergmann\Diff\Differ;
use RectorPrefix20210513\Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20210513\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;
use RectorPrefix20210513\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use function RectorPrefix20210513\Symfony\Component\DependencyInjection\Loader\Configurator\service;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('RectorPrefix20210513\Symplify\\ConsoleColorDiff\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/Bundle']);
    $services->set(\RectorPrefix20210513\SebastianBergmann\Diff\Differ::class);
    $services->set(\RectorPrefix20210513\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory::class);
    $services->set(\RectorPrefix20210513\Symfony\Component\Console\Style\SymfonyStyle::class)->factory([\RectorPrefix20210513\Symfony\Component\DependencyInjection\Loader\Configurator\service(\RectorPrefix20210513\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory::class), 'create']);
    $services->set(\RectorPrefix20210513\Symplify\PackageBuilder\Reflection\PrivatesAccessor::class);
};
