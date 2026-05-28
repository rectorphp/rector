<?php

declare (strict_types=1);
namespace RectorPrefix202605;

use Rector\CodeQuality\Rector\Attribute\SortAttributeNamedArgsRector;
use Rector\CodeQuality\Rector\CallLike\AddNameToBooleanArgumentRector;
use Rector\CodeQuality\Rector\CallLike\AddNameToNullArgumentRector;
use Rector\CodeQuality\Rector\FuncCall\SortCallLikeNamedArgsRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\MethodCall\RemoveNullArgOnNullDefaultParamRector;
use Rector\NetteUtils\Rector\StaticCall\UtilsJsonStaticCallNamedArgRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([AddNameToNullArgumentRector::class, AddNameToBooleanArgumentRector::class, RemoveNullArgOnNullDefaultParamRector::class, SortCallLikeNamedArgsRector::class, SortAttributeNamedArgsRector::class, UtilsJsonStaticCallNamedArgRector::class]);
};
