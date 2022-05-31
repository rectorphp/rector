<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield;
use Rector\Config\RectorConfig;
use Rector\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector;
use Rector\PHPUnit\Rector\Class_\ConstructClassMethodToSetUpTestCaseRector;
use Rector\PHPUnit\Rector\MethodCall\AssertCompareToSpecificMethodRector;
use Rector\PHPUnit\Rector\MethodCall\AssertComparisonToSpecificMethodRector;
use Rector\PHPUnit\Rector\MethodCall\AssertEqualsToSameRector;
use Rector\PHPUnit\Rector\MethodCall\AssertSameTrueFalseToAssertTrueFalseRector;
use Rector\PHPUnit\Rector\MethodCall\RemoveExpectAnyFromMockRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\PHPUnit\Rector\MethodCall\RemoveExpectAnyFromMockRector::class);
    $rectorConfig->rule(\Rector\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector::class, [new \Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield('PHPUnit\\Framework\\TestCase', 'provide*'), new \Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield('PHPUnit\\Framework\\TestCase', 'dataProvider*')]);
    $rectorConfig->rule(\Rector\PHPUnit\Rector\Class_\ConstructClassMethodToSetUpTestCaseRector::class);
    $rectorConfig->rule(\Rector\PHPUnit\Rector\MethodCall\AssertSameTrueFalseToAssertTrueFalseRector::class);
    $rectorConfig->rule(\Rector\PHPUnit\Rector\MethodCall\AssertEqualsToSameRector::class);
    $rectorConfig->rule(\Rector\PHPUnit\Rector\MethodCall\AssertCompareToSpecificMethodRector::class);
    $rectorConfig->rule(\Rector\PHPUnit\Rector\MethodCall\AssertComparisonToSpecificMethodRector::class);
};
