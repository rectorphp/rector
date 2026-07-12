<?php

declare (strict_types=1);
namespace RectorPrefix202607;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\CodeQuality\Rector\Expression\AssertArrayCastedObjectToAssertSameRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertCompareOnCountableWithMethodToAssertCountRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertComparisonToSpecificMethodRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertEmptyNullableObjectToAssertInstanceofRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertEqualsToSameRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertFalseStrposToContainsRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertInstanceOfComparisonRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertIssetToSpecificMethodRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertNotOperatorRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertSameBoolNullToSpecificMethodRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertSameTrueFalseToAssertTrueFalseRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertTrueFalseToSpecificMethodRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\MatchAssertSameExpectedTypeRector;
/**
 * Narrow broad asserts to their specific, more descriptive method calls,
 * e.g. assertTrue(isset($a['b'])) => assertArrayHasKey('b', $a).
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([AssertCompareOnCountableWithMethodToAssertCountRector::class, AssertComparisonToSpecificMethodRector::class, AssertNotOperatorRector::class, AssertTrueFalseToSpecificMethodRector::class, AssertSameBoolNullToSpecificMethodRector::class, AssertFalseStrposToContainsRector::class, AssertIssetToSpecificMethodRector::class, AssertInstanceOfComparisonRector::class, AssertEmptyNullableObjectToAssertInstanceofRector::class, AssertEqualsToSameRector::class, AssertSameTrueFalseToAssertTrueFalseRector::class, MatchAssertSameExpectedTypeRector::class, AssertArrayCastedObjectToAssertSameRector::class]);
};
