<?php

declare (strict_types=1);
namespace RectorPrefix20220209;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\__Package__\Rector\__Category__\__Name__::class);
};
