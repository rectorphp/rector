<?php

declare (strict_types=1);
namespace RectorPrefix202211;

use Rector\Config\RectorConfig;
use Rector\Symfony\Rector\MethodCall\ContainerGetToConstructorInjectionRector;
use Rector\Symfony\Rector\MethodCall\GetParameterToConstructorInjectionRector;
use Rector\Symfony\Rector\MethodCall\GetToConstructorInjectionRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(ContainerGetToConstructorInjectionRector::class);
    $rectorConfig->rule(GetParameterToConstructorInjectionRector::class);
    $rectorConfig->rule(GetToConstructorInjectionRector::class);
};
