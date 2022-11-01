<?php

declare (strict_types=1);
namespace RectorPrefix202211;

use Rector\Config\RectorConfig;
use Rector\Symfony\Rector\BinaryOp\ResponseStatusCodeRector;
use Rector\Symfony\Rector\Class_\EventListenerToEventSubscriberRector;
use Rector\Symfony\Rector\Class_\MakeCommandLazyRector;
use Rector\Symfony\Rector\ClassMethod\ResponseReturnTypeControllerActionRector;
use Rector\Symfony\Rector\MethodCall\LiteralGetToRequestClassConstantRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        MakeCommandLazyRector::class,
        EventListenerToEventSubscriberRector::class,
        ResponseReturnTypeControllerActionRector::class,
        // int and string literals to const fetches
        ResponseStatusCodeRector::class,
        LiteralGetToRequestClassConstantRector::class,
        \Rector\Symfony\Rector\ClassMethod\RemoveUnusedRequestParamRector::class,
    ]);
};
