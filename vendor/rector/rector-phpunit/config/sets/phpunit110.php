<?php

declare (strict_types=1);
namespace RectorPrefix202607;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\PHPUnit110\Rector\CallLike\AssertContainsOnlyMethodCallRector;
use Rector\PHPUnit\PHPUnit110\Rector\Class_\NamedArgumentForDataProviderRector;
use Rector\PHPUnit\PHPUnit120\Rector\Class_\AssertIsTypeMethodCallRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([
        NamedArgumentForDataProviderRector::class,
        // deprecated in PHPUnit 11.5, guarded by composer package constraint
        AssertContainsOnlyMethodCallRector::class,
        AssertIsTypeMethodCallRector::class,
    ]);
};
