<?php

declare (strict_types=1);
namespace RectorPrefix202602;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\PHPUnit120\Rector\CallLike\CreateStubInCoalesceArgRector;
use Rector\PHPUnit\PHPUnit120\Rector\CallLike\CreateStubOverCreateMockArgRector;
use Rector\PHPUnit\PHPUnit120\Rector\Class_\AssertIsTypeMethodCallRector;
use Rector\PHPUnit\PHPUnit120\Rector\Class_\PropertyCreateMockToCreateStubRector;
use Rector\PHPUnit\PHPUnit120\Rector\Class_\RemoveOverrideFinalConstructTestCaseRector;
use Rector\PHPUnit\PHPUnit120\Rector\ClassMethod\ExpressionCreateMockToCreateStubRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([
        RemoveOverrideFinalConstructTestCaseRector::class,
        AssertIsTypeMethodCallRector::class,
        // stubs over mocks
        CreateStubOverCreateMockArgRector::class,
        CreateStubInCoalesceArgRector::class,
        ExpressionCreateMockToCreateStubRector::class,
        PropertyCreateMockToCreateStubRector::class,
    ]);
};
