<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Php74\Rector\Property\TypedPropertyRector::class);
    $services->set(\Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector::class);
};
