<?php

declare(strict_types=1);

use Rector\Php81\Rector\Class_\MyCLabsClassToEnumRector;
use Rector\Php81\Rector\ClassConst\FinalizePublicClassConstantRector;
use Rector\Php81\Rector\MethodCall\MyCLabsMethodCallToEnumConstRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ReturnNeverTypeRector::class);
    $services->set(MyCLabsClassToEnumRector::class);
    $services->set(MyCLabsMethodCallToEnumConstRector::class);
    $services->set(FinalizePublicClassConstantRector::class);
    $services->set(ReadOnlyPropertyRector::class);
};
