<?php

declare (strict_types=1);
namespace RectorPrefix202411;

use Rector\Config\RectorConfig;
use Rector\Symfony\CodeQuality\Rector\BinaryOp\ResponseStatusCodeRector;
use Rector\Symfony\CodeQuality\Rector\Class_\EventListenerToEventSubscriberRector;
use Rector\Symfony\CodeQuality\Rector\Class_\LoadValidatorMetadataToAnnotationRector;
use Rector\Symfony\CodeQuality\Rector\ClassMethod\ActionSuffixRemoverRector;
use Rector\Symfony\CodeQuality\Rector\ClassMethod\ParamTypeFromRouteRequiredRegexRector;
use Rector\Symfony\CodeQuality\Rector\ClassMethod\RemoveUnusedRequestParamRector;
use Rector\Symfony\CodeQuality\Rector\ClassMethod\ResponseReturnTypeControllerActionRector;
use Rector\Symfony\CodeQuality\Rector\MethodCall\AssertSameResponseCodeWithDebugContentsRector;
use Rector\Symfony\CodeQuality\Rector\MethodCall\LiteralGetToRequestClassConstantRector;
use Rector\Symfony\Symfony26\Rector\MethodCall\RedirectToRouteRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        RedirectToRouteRector::class,
        EventListenerToEventSubscriberRector::class,
        ResponseReturnTypeControllerActionRector::class,
        // int and string literals to const fetches
        ResponseStatusCodeRector::class,
        LiteralGetToRequestClassConstantRector::class,
        RemoveUnusedRequestParamRector::class,
        ParamTypeFromRouteRequiredRegexRector::class,
        ActionSuffixRemoverRector::class,
        LoadValidatorMetadataToAnnotationRector::class,
        // tests
        AssertSameResponseCodeWithDebugContentsRector::class,
    ]);
};
