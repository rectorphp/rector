<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire();
    $services->load('RectorPrefix20220607\Symplify\\EasyParallel\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/ValueObject']);
};
