<?php

declare (strict_types=1);
namespace RectorPrefix202609;

use Rector\Config\RectorConfig;
use Rector\Symfony\CodeQuality\Rector\BinaryOp\ResponseStatusCodeRector;
use Rector\Symfony\CodeQuality\Rector\Class_\EventListenerToEventSubscriberRector;
use Rector\Symfony\CodeQuality\Rector\Class_\EventSubscriberMethodReturnVoidRector;
use Rector\Symfony\CodeQuality\Rector\Class_\LoadValidatorMetadataToAttributeRector;
use Rector\Symfony\CodeQuality\Rector\ClassMethod\ParamTypeFromRouteRequiredRegexRector;
use Rector\Symfony\CodeQuality\Rector\ClassMethod\RemoveUnusedRequestParamRector;
use Rector\Symfony\CodeQuality\Rector\ClassMethod\ResponseReturnTypeControllerActionRector;
use Rector\Symfony\CodeQuality\Rector\MethodCall\AssertSameResponseCodeWithDebugContentsRector;
use Rector\Symfony\CodeQuality\Rector\MethodCall\StringCastDebugResponseRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([
        EventListenerToEventSubscriberRector::class,
        ResponseReturnTypeControllerActionRector::class,
        EventSubscriberMethodReturnVoidRector::class,
        // int and string literals to const fetches
        ResponseStatusCodeRector::class,
        RemoveUnusedRequestParamRector::class,
        ParamTypeFromRouteRequiredRegexRector::class,
        // controller
        LoadValidatorMetadataToAttributeRector::class,
        // tests
        AssertSameResponseCodeWithDebugContentsRector::class,
        StringCastDebugResponseRector::class,
    ]);
};
