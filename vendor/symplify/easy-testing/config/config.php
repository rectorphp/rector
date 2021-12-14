<?php

declare (strict_types=1);
namespace RectorPrefix20211214;

use RectorPrefix20211214\Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20211214\Symplify\EasyTesting\Command\ValidateFixtureSkipNamingCommand;
use function RectorPrefix20211214\Symfony\Component\DependencyInjection\Loader\Configurator\service;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('RectorPrefix20211214\Symplify\\EasyTesting\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/DataProvider', __DIR__ . '/../src/Kernel', __DIR__ . '/../src/ValueObject']);
    // console
    $services->set(\RectorPrefix20211214\Symfony\Component\Console\Application::class)->call('add', [\RectorPrefix20211214\Symfony\Component\DependencyInjection\Loader\Configurator\service(\RectorPrefix20211214\Symplify\EasyTesting\Command\ValidateFixtureSkipNamingCommand::class)]);
};
