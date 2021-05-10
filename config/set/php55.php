<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use Rector\Php55\Rector\Class_\ClassConstantToSelfClassRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(StringClassNameToClassConstantRector::class);
    $services->set(ClassConstantToSelfClassRector::class);
};
