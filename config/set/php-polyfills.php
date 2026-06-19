<?php

declare (strict_types=1);
namespace RectorPrefix202606;

use Rector\Config\RectorConfig;
use Rector\Php73\Rector\BooleanOr\IsCountableRector;
use Rector\Php73\Rector\FuncCall\ArrayKeyFirstLastRector;
use Rector\Php80\Rector\Identical\StrEndsWithRector;
use Rector\Php80\Rector\Identical\StrStartsWithRector;
use Rector\Php80\Rector\NotIdentical\MbStrContainsRector;
use Rector\Php80\Rector\NotIdentical\StrContainsRector;
use Rector\Php80\Rector\Ternary\GetDebugTypeRector;
use Rector\Php83\Rector\BooleanAnd\JsonValidateRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\Php84\Rector\Class_\DeprecatedAnnotationToDeprecatedAttributeRector;
use Rector\Php84\Rector\Foreach_\ForeachToArrayAllRector;
use Rector\Php84\Rector\Foreach_\ForeachToArrayAnyRector;
use Rector\Php84\Rector\Foreach_\ForeachToArrayFindKeyRector;
use Rector\Php84\Rector\Foreach_\ForeachToArrayFindRector;
// @note longer rule registration must be used here, to separate from withRules() from root rector.php
// these rules can be used ahead of PHP version,
// as long composer.json includes particular symfony/php-polyfill package
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([
        ArrayKeyFirstLastRector::class,
        IsCountableRector::class,
        GetDebugTypeRector::class,
        StrStartsWithRector::class,
        StrEndsWithRector::class,
        StrContainsRector::class,
        MbStrContainsRector::class,
        // PHP 8.3
        JsonValidateRector::class,
        AddOverrideAttributeToOverriddenMethodsRector::class,
        // PHP 8.4
        ForeachToArrayAllRector::class,
        ForeachToArrayAnyRector::class,
        ForeachToArrayFindRector::class,
        ForeachToArrayFindKeyRector::class,
        DeprecatedAnnotationToDeprecatedAttributeRector::class,
    ]);
};
