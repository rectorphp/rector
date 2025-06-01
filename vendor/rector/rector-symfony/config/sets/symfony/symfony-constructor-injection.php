<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Symfony\DependencyInjection\Rector\Class_\CommandGetByTypeToConstructorInjectionRector;
use Rector\Symfony\DependencyInjection\Rector\Class_\ControllerGetByTypeToConstructorInjectionRector;
use Rector\Symfony\DependencyInjection\Rector\Class_\GetBySymfonyStringToConstructorInjectionRector;
use Rector\Symfony\DependencyInjection\Rector\Trait_\TraitGetByTypeToInjectRector;
use Rector\Symfony\Symfony28\Rector\MethodCall\GetToConstructorInjectionRector;
use Rector\Symfony\Symfony34\Rector\Closure\ContainerGetNameToTypeInTestsRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        // modern step-by-step narrow approach
        ControllerGetByTypeToConstructorInjectionRector::class,
        CommandGetByTypeToConstructorInjectionRector::class,
        GetBySymfonyStringToConstructorInjectionRector::class,
        TraitGetByTypeToInjectRector::class,
        // legacy rules that require container fetch
        ContainerGetNameToTypeInTestsRector::class,
        GetToConstructorInjectionRector::class,
    ]);
};
