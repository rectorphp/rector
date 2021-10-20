<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use Rector\Php55\Rector\Class_\ClassConstantToSelfClassRector;
use Rector\Php55\Rector\FuncCall\PregReplaceEModifierRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Php55\Rector\String_\StringClassNameToClassConstantRector::class);
    $services->set(\Rector\Php55\Rector\Class_\ClassConstantToSelfClassRector::class);
    $services->set(\Rector\Php55\Rector\FuncCall\PregReplaceEModifierRector::class);
};
