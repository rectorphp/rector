<?php

declare (strict_types=1);
namespace RectorPrefix202301;

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
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(RemoveExpectAnyFromMockRector::class);
    $rectorConfig->rule(AddSeeTestAnnotationRector::class);
    $rectorConfig->ruleWithConfiguration(ReturnArrayClassMethodToYieldRector::class, [new ReturnArrayClassMethodToYield('PHPUnit\\Framework\\TestCase', 'provide*'), new ReturnArrayClassMethodToYield('PHPUnit\\Framework\\TestCase', 'dataProvider*')]);
    $rectorConfig->rule(ConstructClassMethodToSetUpTestCaseRector::class);
    $rectorConfig->rule(AssertSameTrueFalseToAssertTrueFalseRector::class);
    $rectorConfig->rule(AssertEqualsToSameRector::class);
    $rectorConfig->rule(AssertCompareToSpecificMethodRector::class);
    $rectorConfig->rule(AssertComparisonToSpecificMethodRector::class);
};
