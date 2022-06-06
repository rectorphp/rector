<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Privatization\Rector\Class_\ChangeGlobalVariablesToPropertiesRector;
use RectorPrefix20220606\Rector\Privatization\Rector\Class_\ChangeLocalPropertyToVariableRector;
use RectorPrefix20220606\Rector\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector;
use RectorPrefix20220606\Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use RectorPrefix20220606\Rector\Privatization\Rector\Class_\RepeatedLiteralToClassConstantRector;
use RectorPrefix20220606\Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector;
use RectorPrefix20220606\Rector\Privatization\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector;
use RectorPrefix20220606\Rector\Privatization\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector;
use RectorPrefix20220606\Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(FinalizeClassesWithoutChildrenRector::class);
    $rectorConfig->rule(ChangeGlobalVariablesToPropertiesRector::class);
    $rectorConfig->rule(ChangeReadOnlyPropertyWithDefaultValueToConstantRector::class);
    $rectorConfig->rule(ChangeReadOnlyVariableWithDefaultValueToConstantRector::class);
    $rectorConfig->rule(RepeatedLiteralToClassConstantRector::class);
    $rectorConfig->rule(PrivatizeLocalGetterToPropertyRector::class);
    $rectorConfig->rule(PrivatizeFinalClassPropertyRector::class);
    $rectorConfig->rule(PrivatizeFinalClassMethodRector::class);
    // buggy, requires more work
    // $services->set(ChangeLocalPropertyToVariableRector::class);
};
