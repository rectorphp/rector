<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use Rector\Symfony\Rector\ClassMethod\RemoveServiceFromSensioRouteRector;
use Rector\Symfony\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(ReplaceSensioRouteAnnotationWithSymfonyRector::class);
    $services->set(RemoveServiceFromSensioRouteRector::class);
};
