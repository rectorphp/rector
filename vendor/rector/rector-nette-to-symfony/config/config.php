<?php

declare (strict_types=1);
namespace RectorPrefix20210520;

use RectorPrefix20210520\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20210520\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
return static function (\RectorPrefix20210520\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('Rector\\NetteToSymfony\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/Rector', __DIR__ . '/../src/ValueObject']);
    $services->set(\RectorPrefix20210520\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser::class);
};
