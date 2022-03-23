<?php

declare (strict_types=1);
namespace RectorPrefix20220323;

use Rector\Symfony\Rector\BinaryOp\ResponseStatusCodeRector;
use Rector\Symfony\Rector\Class_\EventListenerToEventSubscriberRector;
use Rector\Symfony\Rector\Class_\MakeCommandLazyRector;
use Rector\Symfony\Rector\ClassMethod\ResponseReturnTypeControllerActionRector;
use Rector\Symfony\Rector\MethodCall\LiteralGetToRequestClassConstantRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Symfony\Rector\Class_\MakeCommandLazyRector::class);
    $services->set(\Rector\Symfony\Rector\Class_\EventListenerToEventSubscriberRector::class);
    $services->set(\Rector\Symfony\Rector\ClassMethod\ResponseReturnTypeControllerActionRector::class);
    // int and string literals to const fetches
    $services->set(\Rector\Symfony\Rector\BinaryOp\ResponseStatusCodeRector::class);
    $services->set(\Rector\Symfony\Rector\MethodCall\LiteralGetToRequestClassConstantRector::class);
};
