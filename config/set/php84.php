<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Php84\Rector\Class_\DeprecatedAnnotationToDeprecatedAttributeRector;
use Rector\Php84\Rector\Foreach_\ForeachToArrayAllRector;
use Rector\Php84\Rector\Foreach_\ForeachToArrayAnyRector;
use Rector\Php84\Rector\Foreach_\ForeachToArrayFindKeyRector;
use Rector\Php84\Rector\Foreach_\ForeachToArrayFindRector;
use Rector\Php84\Rector\FuncCall\AddEscapeArgumentRector;
use Rector\Php84\Rector\FuncCall\RoundingModeEnumRector;
use Rector\Php84\Rector\MethodCall\NewMethodCallWithoutParenthesesRector;
use Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([ExplicitNullableParamTypeRector::class, RoundingModeEnumRector::class, AddEscapeArgumentRector::class, NewMethodCallWithoutParenthesesRector::class, DeprecatedAnnotationToDeprecatedAttributeRector::class, ForeachToArrayFindRector::class, ForeachToArrayFindKeyRector::class, ForeachToArrayAllRector::class, ForeachToArrayAnyRector::class]);
};
