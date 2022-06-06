<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Symfony\Rector\MethodCall\ContainerGetToConstructorInjectionRector;
use RectorPrefix20220606\Rector\Symfony\Rector\MethodCall\GetParameterToConstructorInjectionRector;
use RectorPrefix20220606\Rector\Symfony\Rector\MethodCall\GetToConstructorInjectionRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(ContainerGetToConstructorInjectionRector::class);
    $rectorConfig->rule(GetParameterToConstructorInjectionRector::class);
    $rectorConfig->rule(GetToConstructorInjectionRector::class);
};
