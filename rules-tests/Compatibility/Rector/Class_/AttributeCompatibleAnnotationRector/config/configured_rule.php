<?php

declare(strict_types=1);

use Rector\Compatibility\Rector\Class_\AttributeCompatibleAnnotationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(AttributeCompatibleAnnotationRector::class);
};
