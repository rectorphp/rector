<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\CodeQuality\Rector\Class_\ConstructClassMethodToSetUpTestCaseRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\NarrowUnusedSetUpDefinedPropertyRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\RemoveDataProviderParamKeysRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\SingleMockPropertyTypeRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\TestWithToDataProviderRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\TypeWillReturnCallableArrowFunctionRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\YieldDataProviderRector;
use Rector\PHPUnit\CodeQuality\Rector\ClassMethod\AddInstanceofAssertForNullableInstanceRector;
use Rector\PHPUnit\CodeQuality\Rector\ClassMethod\DataProviderArrayItemsNewLinedRector;
use Rector\PHPUnit\CodeQuality\Rector\ClassMethod\RemoveEmptyTestMethodRector;
use Rector\PHPUnit\CodeQuality\Rector\ClassMethod\ReplaceTestAnnotationWithPrefixedFunctionRector;
use Rector\PHPUnit\CodeQuality\Rector\Foreach_\SimplifyForeachInstanceOfRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertCompareOnCountableWithMethodToAssertCountRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertComparisonToSpecificMethodRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertEmptyNullableObjectToAssertInstanceofRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertEqualsOrAssertSameFloatParameterToSpecificMethodsTypeRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertEqualsToSameRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertFalseStrposToContainsRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertInstanceOfComparisonRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertIssetToSpecificMethodRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertNotOperatorRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertPropertyExistsRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertRegExpRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertSameBoolNullToSpecificMethodRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertSameTrueFalseToAssertTrueFalseRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertTrueFalseToSpecificMethodRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\FlipAssertRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\NarrowIdenticalWithConsecutiveRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\NarrowSingleWillReturnCallbackRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\RemoveExpectAnyFromMockRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\SingleWithConsecutiveToWithRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\UseSpecificWillMethodRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\UseSpecificWithMethodRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        ConstructClassMethodToSetUpTestCaseRector::class,
        AssertSameTrueFalseToAssertTrueFalseRector::class,
        AssertEqualsToSameRector::class,
        PreferPHPUnitThisCallRector::class,
        YieldDataProviderRector::class,
        RemoveEmptyTestMethodRector::class,
        ReplaceTestAnnotationWithPrefixedFunctionRector::class,
        TestWithToDataProviderRector::class,
        AssertEqualsOrAssertSameFloatParameterToSpecificMethodsTypeRector::class,
        DataProviderArrayItemsNewLinedRector::class,
        // PHPUnit 11 reports warnings on typos + keys are rather noise than useful, fake system-keys for values
        RemoveDataProviderParamKeysRector::class,
        FlipAssertRector::class,
        // narrow with consecutive
        NarrowIdenticalWithConsecutiveRector::class,
        NarrowSingleWillReturnCallbackRector::class,
        SingleWithConsecutiveToWithRector::class,
        // type declarations
        TypeWillReturnCallableArrowFunctionRector::class,
        NarrowUnusedSetUpDefinedPropertyRector::class,
        // specific asserts
        AssertCompareOnCountableWithMethodToAssertCountRector::class,
        AssertComparisonToSpecificMethodRector::class,
        AssertNotOperatorRector::class,
        AssertTrueFalseToSpecificMethodRector::class,
        AssertSameBoolNullToSpecificMethodRector::class,
        AssertFalseStrposToContainsRector::class,
        AssertIssetToSpecificMethodRector::class,
        AssertInstanceOfComparisonRector::class,
        AssertPropertyExistsRector::class,
        AssertRegExpRector::class,
        SimplifyForeachInstanceOfRector::class,
        UseSpecificWillMethodRector::class,
        UseSpecificWithMethodRector::class,
        AssertEmptyNullableObjectToAssertInstanceofRector::class,
        // avoid call on nullable object
        AddInstanceofAssertForNullableInstanceRector::class,
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
        RemoveExpectAnyFromMockRector::class,
        SingleMockPropertyTypeRector::class,
    ]);
};
