<?php

declare (strict_types=1);
namespace RectorPrefix20220527;

use Rector\Config\RectorConfig;
use Rector\Symfony\Rector\BinaryOp\ResponseStatusCodeRector;
use Rector\Symfony\Rector\Class_\EventListenerToEventSubscriberRector;
use Rector\Symfony\Rector\Class_\MakeCommandLazyRector;
use Rector\Symfony\Rector\ClassMethod\ResponseReturnTypeControllerActionRector;
use Rector\Symfony\Rector\MethodCall\LiteralGetToRequestClassConstantRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(MakeCommandLazyRector::class);
    $rectorConfig->rule(EventListenerToEventSubscriberRector::class);
    $rectorConfig->rule(ResponseReturnTypeControllerActionRector::class);
    // int and string literals to const fetches
    $rectorConfig->rule(ResponseStatusCodeRector::class);
    $rectorConfig->rule(LiteralGetToRequestClassConstantRector::class);
};
