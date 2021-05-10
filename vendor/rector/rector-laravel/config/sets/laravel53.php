<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use Rector\Removing\Rector\Class_\RemoveTraitRector;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(RemoveTraitRector::class)->call('configure', [[RemoveTraitRector::TRAITS_TO_REMOVE => [
        # see https://laravel.com/docs/5.3/upgrade
        'Illuminate\\Foundation\\Auth\\Access\\AuthorizesResources',
    ]]]);
};
