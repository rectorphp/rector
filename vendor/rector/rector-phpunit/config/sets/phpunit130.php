<?php

declare (strict_types=1);
namespace RectorPrefix202608;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig): void {
    // AssertContainsOnlyMethodCallRector and AssertIsTypeMethodCallRector are registered there,
    // guarded by composer package constraint
    $rectorConfig->sets([PHPUnitSetList::COMPOSER_BASED]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // @see https://github.com/sebastianbergmann/phpunit/issues/6560
        new MethodCallRename('PHPUnit\Framework\TestCase', 'expectExceptionMessage', 'expectExceptionMessageIsOrContains'),
    ]);
};
