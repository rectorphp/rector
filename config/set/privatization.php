<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Privatization\Rector\Class_\ChangeGlobalVariablesToPropertiesRector;
use Rector\Privatization\Rector\Class_\ChangeLocalPropertyToVariableRector;
use Rector\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Privatization\Rector\Class_\RepeatedLiteralToClassConstantRector;
use Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector;
use Rector\Privatization\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector;
use Rector\Privatization\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector;
use Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(FinalizeClassesWithoutChildrenRector::class);
    $services->set(ChangeGlobalVariablesToPropertiesRector::class);
    $services->set(ChangeReadOnlyPropertyWithDefaultValueToConstantRector::class);
    $services->set(ChangeReadOnlyVariableWithDefaultValueToConstantRector::class);
    $services->set(RepeatedLiteralToClassConstantRector::class);
    $services->set(PrivatizeLocalGetterToPropertyRector::class);
    $services->set(PrivatizeFinalClassPropertyRector::class);
    $services->set(PrivatizeFinalClassMethodRector::class);

    // buggy, requires more work
    // $services->set(ChangeLocalPropertyToVariableRector::class);
};
