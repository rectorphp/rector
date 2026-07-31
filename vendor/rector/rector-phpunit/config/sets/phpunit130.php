<?php

declare (strict_types=1);
namespace RectorPrefix202607;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\PHPUnit110\Rector\CallLike\AssertContainsOnlyMethodCallRector;
use Rector\PHPUnit\PHPUnit120\Rector\Class_\AssertIsTypeMethodCallRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([
        // deprecated in PHPUnit 11.5, repeated here for a direct upgrade from an older version
        AssertContainsOnlyMethodCallRector::class,
        AssertIsTypeMethodCallRector::class,
    ]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // @see https://github.com/sebastianbergmann/phpunit/issues/6560
        new MethodCallRename('PHPUnit\Framework\TestCase', 'expectExceptionMessage', 'expectExceptionMessageIsOrContains'),
    ]);
};
