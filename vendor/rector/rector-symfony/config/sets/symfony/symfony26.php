<?php

declare (strict_types=1);
namespace RectorPrefix20211231;

use Rector\Symfony\Rector\MethodCall\AddFlashRector;
use Rector\Symfony\Rector\MethodCall\RedirectToRouteRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Symfony\Rector\MethodCall\RedirectToRouteRector::class);
    $services->set(\Rector\Symfony\Rector\MethodCall\AddFlashRector::class);
};
