<?php

declare(strict_types=1);

use Rector\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\MakeGetterClassMethodNameStartWithGetRector;
use Rector\Naming\Rector\ClassMethod\MakeIsserClassMethodNameStartWithIsRector;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RenameParamToMatchTypeRector::class);
    $services->set(RenamePropertyToMatchTypeRector::class);
    $services->set(RenameVariableToMatchNewTypeRector::class);
    $services->set(RenameVariableToMatchMethodCallReturnTypeRector::class);
    $services->set(MakeGetterClassMethodNameStartWithGetRector::class);
    $services->set(MakeIsserClassMethodNameStartWithIsRector::class);
};
