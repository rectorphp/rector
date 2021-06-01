<?php

declare (strict_types=1);
namespace RectorPrefix20210601;

use RectorPrefix20210601\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210601\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('Rector\\Doctrine\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/Rector']);
};
