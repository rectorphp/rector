<?php

declare (strict_types=1);
namespace RectorPrefix202608;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\PHPUnit110\Rector\Class_\NamedArgumentForDataProviderRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
return static function (RectorConfig $rectorConfig): void {
    // MockObjectArgCreateStubToCreateMockRector, AssertContainsOnlyMethodCallRector
    // and AssertIsTypeMethodCallRector are registered there, guarded by composer package constraint
    $rectorConfig->sets([PHPUnitSetList::COMPOSER_BASED]);
    $rectorConfig->rules([NamedArgumentForDataProviderRector::class]);
};
