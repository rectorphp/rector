<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use Rector\Removing\Rector\Class_\RemoveTraitUseRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Removing\Rector\Class_\RemoveTraitUseRector::class)->call('configure', [[\Rector\Removing\Rector\Class_\RemoveTraitUseRector::TRAITS_TO_REMOVE => [
        # see https://laravel.com/docs/5.3/upgrade
        'Illuminate\\Foundation\\Auth\\Access\\AuthorizesResources',
    ]]]);
};
