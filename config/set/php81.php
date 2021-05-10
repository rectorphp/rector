<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(ReturnNeverTypeRector::class);
};
