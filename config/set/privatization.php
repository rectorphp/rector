<?php

declare (strict_types=1);
namespace RectorPrefix202308;

use Rector\Config\RectorConfig;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector;
use Rector\Privatization\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector;
use Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([FinalizeClassesWithoutChildrenRector::class, PrivatizeLocalGetterToPropertyRector::class, PrivatizeFinalClassPropertyRector::class, PrivatizeFinalClassMethodRector::class]);
};
