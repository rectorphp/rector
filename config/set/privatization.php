<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

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
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector::class);
    $rectorConfig->rule(\Rector\Privatization\Rector\Class_\ChangeGlobalVariablesToPropertiesRector::class);
    $rectorConfig->rule(\Rector\Privatization\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector::class);
    $rectorConfig->rule(\Rector\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector::class);
    $rectorConfig->rule(\Rector\Privatization\Rector\Class_\RepeatedLiteralToClassConstantRector::class);
    $rectorConfig->rule(\Rector\Privatization\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector::class);
    $rectorConfig->rule(\Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector::class);
    $rectorConfig->rule(\Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector::class);
    // buggy, requires more work
    // $services->set(ChangeLocalPropertyToVariableRector::class);
};
