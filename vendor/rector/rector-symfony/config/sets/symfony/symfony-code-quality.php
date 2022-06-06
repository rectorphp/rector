<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Symfony\Rector\BinaryOp\ResponseStatusCodeRector;
use RectorPrefix20220606\Rector\Symfony\Rector\Class_\EventListenerToEventSubscriberRector;
use RectorPrefix20220606\Rector\Symfony\Rector\Class_\MakeCommandLazyRector;
use RectorPrefix20220606\Rector\Symfony\Rector\ClassMethod\ResponseReturnTypeControllerActionRector;
use RectorPrefix20220606\Rector\Symfony\Rector\MethodCall\LiteralGetToRequestClassConstantRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        MakeCommandLazyRector::class,
        EventListenerToEventSubscriberRector::class,
        ResponseReturnTypeControllerActionRector::class,
        // int and string literals to const fetches
        ResponseStatusCodeRector::class,
        LiteralGetToRequestClassConstantRector::class,
    ]);
};
