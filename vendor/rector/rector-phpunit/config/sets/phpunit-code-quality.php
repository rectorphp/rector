<?php

declare (strict_types=1);
namespace RectorPrefix202307;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector;
use Rector\PHPUnit\Rector\Class_\ConstructClassMethodToSetUpTestCaseRector;
use Rector\PHPUnit\Rector\Class_\PreferPHPUnitThisCallRector;
use Rector\PHPUnit\Rector\Class_\YieldDataProviderRector;
use Rector\PHPUnit\Rector\Foreach_\SimplifyForeachInstanceOfRector;
use Rector\PHPUnit\Rector\MethodCall\AssertCompareToSpecificMethodRector;
use Rector\PHPUnit\Rector\MethodCall\AssertComparisonToSpecificMethodRector;
use Rector\PHPUnit\Rector\MethodCall\AssertEqualsToSameRector;
use Rector\PHPUnit\Rector\MethodCall\AssertFalseStrposToContainsRector;
use Rector\PHPUnit\Rector\MethodCall\AssertInstanceOfComparisonRector;
use Rector\PHPUnit\Rector\MethodCall\AssertIssetToSpecificMethodRector;
use Rector\PHPUnit\Rector\MethodCall\AssertNotOperatorRector;
use Rector\PHPUnit\Rector\MethodCall\AssertPropertyExistsRector;
use Rector\PHPUnit\Rector\MethodCall\AssertRegExpRector;
use Rector\PHPUnit\Rector\MethodCall\AssertSameBoolNullToSpecificMethodRector;
use Rector\PHPUnit\Rector\MethodCall\AssertSameTrueFalseToAssertTrueFalseRector;
use Rector\PHPUnit\Rector\MethodCall\AssertTrueFalseInternalTypeToSpecificMethodRector;
use Rector\PHPUnit\Rector\MethodCall\AssertTrueFalseToSpecificMethodRector;
use Rector\PHPUnit\Rector\MethodCall\RemoveExpectAnyFromMockRector;
use Rector\PHPUnit\Rector\MethodCall\RemoveSetMethodsMethodCallRector;
use Rector\PHPUnit\Rector\MethodCall\UseSpecificWillMethodRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        AddSeeTestAnnotationRector::class,
        ConstructClassMethodToSetUpTestCaseRector::class,
        AssertSameTrueFalseToAssertTrueFalseRector::class,
        AssertEqualsToSameRector::class,
        PreferPHPUnitThisCallRector::class,
        YieldDataProviderRector::class,
        // sepcific asserts
        AssertCompareToSpecificMethodRector::class,
        AssertComparisonToSpecificMethodRector::class,
        AssertNotOperatorRector::class,
        AssertTrueFalseToSpecificMethodRector::class,
        AssertSameBoolNullToSpecificMethodRector::class,
        AssertFalseStrposToContainsRector::class,
        AssertTrueFalseInternalTypeToSpecificMethodRector::class,
        AssertIssetToSpecificMethodRector::class,
        AssertInstanceOfComparisonRector::class,
        AssertPropertyExistsRector::class,
        AssertRegExpRector::class,
        SimplifyForeachInstanceOfRector::class,
        UseSpecificWillMethodRector::class,
        /**
         * Improve direct testing of your code, without mock creep. Make it simple, clear and easy to maintain:
         *
         * @see https://blog.frankdejonge.nl/testing-without-mocking-frameworks/
         * @see https://maksimivanov.com/posts/dont-mock-what-you-dont-own/
         * @see https://dev.to/mguinea/stop-using-mocking-libraries-2f2k
         * @see https://mnapoli.fr/anonymous-classes-in-tests/
         * @see https://steemit.com/php/@crell/don-t-use-mocking-libraries
         * @see https://davegebler.com/post/php/better-php-unit-testing-avoiding-mocks
         */
        RemoveSetMethodsMethodCallRector::class,
        RemoveExpectAnyFromMockRector::class,
    ]);
};
