<?php

declare (strict_types=1);
namespace RectorPrefix20210827;

use Rector\Php81\Rector\Class_\MyCLabsClassToEnumRector;
use Rector\Php81\Rector\ClassConst\FinalizePublicClassConstantRector;
use Rector\Php81\Rector\MethodCall\MyCLabsMethodCallToEnumConstRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector::class);
    $services->set(\Rector\Php81\Rector\Class_\MyCLabsClassToEnumRector::class);
    $services->set(\Rector\Php81\Rector\MethodCall\MyCLabsMethodCallToEnumConstRector::class);
    $services->set(\Rector\Php81\Rector\ClassConst\FinalizePublicClassConstantRector::class);
    $services->set(\Rector\Php81\Rector\Property\ReadOnlyPropertyRector::class);
};
