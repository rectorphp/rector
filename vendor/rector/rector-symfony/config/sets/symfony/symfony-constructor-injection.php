<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Symfony\Rector\MethodCall\ContainerGetToConstructorInjectionRector;
use Rector\Symfony\Rector\MethodCall\GetParameterToConstructorInjectionRector;
use Rector\Symfony\Rector\MethodCall\GetToConstructorInjectionRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\Symfony\Rector\MethodCall\ContainerGetToConstructorInjectionRector::class);
    $rectorConfig->rule(\Rector\Symfony\Rector\MethodCall\GetParameterToConstructorInjectionRector::class);
    $rectorConfig->rule(\Rector\Symfony\Rector\MethodCall\GetToConstructorInjectionRector::class);
};
