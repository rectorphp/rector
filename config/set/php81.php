<?php

declare (strict_types=1);
namespace RectorPrefix20210513;

use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
use RectorPrefix20210513\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210513\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector::class);
};
