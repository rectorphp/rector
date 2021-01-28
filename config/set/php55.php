<?php

declare(strict_types=1);

use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\Php55\Rector\Class_\ClassConstantToSelfClassRector;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(StringClassNameToClassConstantRector::class);
    $services->set(ClassConstantToSelfClassRector::class);
};
