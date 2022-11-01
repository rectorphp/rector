<?php

declare (strict_types=1);
namespace RectorPrefix202211;

use Rector\Config\RectorConfig;
use Rector\Privatization\Rector\Class_\ChangeGlobalVariablesToPropertiesRector;
use Rector\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector;
use Rector\Privatization\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector;
use Rector\Privatization\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector;
use Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(FinalizeClassesWithoutChildrenRector::class);
    $rectorConfig->rule(ChangeGlobalVariablesToPropertiesRector::class);
    $rectorConfig->rule(ChangeReadOnlyPropertyWithDefaultValueToConstantRector::class);
    $rectorConfig->rule(ChangeReadOnlyVariableWithDefaultValueToConstantRector::class);
    $rectorConfig->rule(PrivatizeLocalGetterToPropertyRector::class);
    $rectorConfig->rule(PrivatizeFinalClassPropertyRector::class);
    $rectorConfig->rule(PrivatizeFinalClassMethodRector::class);
};
