<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use RectorPrefix20220606\Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield;
use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector;
use RectorPrefix20220606\Rector\PHPUnit\Rector\Class_\ConstructClassMethodToSetUpTestCaseRector;
use RectorPrefix20220606\Rector\PHPUnit\Rector\MethodCall\AssertCompareToSpecificMethodRector;
use RectorPrefix20220606\Rector\PHPUnit\Rector\MethodCall\AssertComparisonToSpecificMethodRector;
use RectorPrefix20220606\Rector\PHPUnit\Rector\MethodCall\AssertEqualsToSameRector;
use RectorPrefix20220606\Rector\PHPUnit\Rector\MethodCall\AssertSameTrueFalseToAssertTrueFalseRector;
use RectorPrefix20220606\Rector\PHPUnit\Rector\MethodCall\RemoveExpectAnyFromMockRector;
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
