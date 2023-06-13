<?php

declare (strict_types=1);
namespace RectorPrefix202306;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector;
use Rector\PHPUnit\Rector\Class_\ConstructClassMethodToSetUpTestCaseRector;
use Rector\PHPUnit\Rector\Class_\PreferPHPUnitThisCallRector;
use Rector\PHPUnit\Rector\Class_\YieldDataProviderRector;
use Rector\PHPUnit\Rector\MethodCall\AssertCompareToSpecificMethodRector;
use Rector\PHPUnit\Rector\MethodCall\AssertComparisonToSpecificMethodRector;
use Rector\PHPUnit\Rector\MethodCall\AssertEqualsToSameRector;
use Rector\PHPUnit\Rector\MethodCall\AssertSameTrueFalseToAssertTrueFalseRector;
use Rector\PHPUnit\Rector\MethodCall\RemoveExpectAnyFromMockRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([RemoveExpectAnyFromMockRector::class, AddSeeTestAnnotationRector::class, ConstructClassMethodToSetUpTestCaseRector::class, AssertSameTrueFalseToAssertTrueFalseRector::class, AssertEqualsToSameRector::class, AssertCompareToSpecificMethodRector::class, AssertComparisonToSpecificMethodRector::class, PreferPHPUnitThisCallRector::class, YieldDataProviderRector::class]);
};
