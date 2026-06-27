<?php

declare (strict_types=1);
namespace RectorPrefix202606;

use Rector\CodeQuality\Rector\Attribute\ExplicitAttributeNamedArgsRector;
use Rector\CodeQuality\Rector\Attribute\SortAttributeNamedArgsRector;
use Rector\CodeQuality\Rector\CallLike\AddNameToBooleanArgumentRector;
use Rector\CodeQuality\Rector\CallLike\AddNameToNullArgumentRector;
use Rector\CodeQuality\Rector\FuncCall\SortCallLikeNamedArgsRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\MethodCall\RemoveNullNamedArgOnNullDefaultParamRector;
use Rector\NetteUtils\Rector\StaticCall\UtilsJsonStaticCallNamedArgRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([AddNameToNullArgumentRector::class, AddNameToBooleanArgumentRector::class, RemoveNullNamedArgOnNullDefaultParamRector::class, SortCallLikeNamedArgsRector::class, SortAttributeNamedArgsRector::class, ExplicitAttributeNamedArgsRector::class, UtilsJsonStaticCallNamedArgRector::class]);
};
