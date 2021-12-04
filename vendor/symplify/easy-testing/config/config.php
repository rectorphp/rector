<?php

declare (strict_types=1);
namespace RectorPrefix20211204;

use RectorPrefix20211204\Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20211204\Symplify\EasyTesting\Console\EasyTestingConsoleApplication;
use RectorPrefix20211204\Symplify\PackageBuilder\Console\Command\CommandNaming;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('RectorPrefix20211204\Symplify\\EasyTesting\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/DataProvider', __DIR__ . '/../src/Kernel', __DIR__ . '/../src/ValueObject']);
    // console
    $services->set(\RectorPrefix20211204\Symplify\EasyTesting\Console\EasyTestingConsoleApplication::class);
    $services->alias(\RectorPrefix20211204\Symfony\Component\Console\Application::class, \RectorPrefix20211204\Symplify\EasyTesting\Console\EasyTestingConsoleApplication::class);
    $services->set(\RectorPrefix20211204\Symplify\PackageBuilder\Console\Command\CommandNaming::class);
};
