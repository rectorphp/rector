<?php

declare (strict_types=1);
namespace RectorPrefix202306;

use RectorPrefix202306\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire();
    $services->load('RectorPrefix202306\Symplify\\EasyParallel\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/ValueObject', __DIR__ . '/../src/Enum', __DIR__ . '/../src/Exception', __DIR__ . '/../src/Contract']);
};
