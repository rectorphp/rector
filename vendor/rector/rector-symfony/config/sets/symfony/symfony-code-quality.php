<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Symfony\Rector\BinaryOp\ResponseStatusCodeRector;
use Rector\Symfony\Rector\Class_\EventListenerToEventSubscriberRector;
use Rector\Symfony\Rector\Class_\MakeCommandLazyRector;
use Rector\Symfony\Rector\ClassMethod\ResponseReturnTypeControllerActionRector;
use Rector\Symfony\Rector\MethodCall\LiteralGetToRequestClassConstantRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\Symfony\Rector\Class_\MakeCommandLazyRector::class);
    $rectorConfig->rule(\Rector\Symfony\Rector\Class_\EventListenerToEventSubscriberRector::class);
    $rectorConfig->rule(\Rector\Symfony\Rector\ClassMethod\ResponseReturnTypeControllerActionRector::class);
    // int and string literals to const fetches
    $rectorConfig->rule(\Rector\Symfony\Rector\BinaryOp\ResponseStatusCodeRector::class);
    $rectorConfig->rule(\Rector\Symfony\Rector\MethodCall\LiteralGetToRequestClassConstantRector::class);
};
