<?php

declare (strict_types=1);
namespace RectorPrefix202212;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Rector\Foreach_\SimplifyForeachInstanceOfRector;
use Rector\PHPUnit\Rector\MethodCall\AssertCompareToSpecificMethodRector;
use Rector\PHPUnit\Rector\MethodCall\AssertComparisonToSpecificMethodRector;
use Rector\PHPUnit\Rector\MethodCall\AssertFalseStrposToContainsRector;
use Rector\PHPUnit\Rector\MethodCall\AssertInstanceOfComparisonRector;
use Rector\PHPUnit\Rector\MethodCall\AssertIssetToSpecificMethodRector;
use Rector\PHPUnit\Rector\MethodCall\AssertNotOperatorRector;
use Rector\PHPUnit\Rector\MethodCall\AssertPropertyExistsRector;
use Rector\PHPUnit\Rector\MethodCall\AssertRegExpRector;
use Rector\PHPUnit\Rector\MethodCall\AssertSameBoolNullToSpecificMethodRector;
use Rector\PHPUnit\Rector\MethodCall\AssertTrueFalseInternalTypeToSpecificMethodRector;
use Rector\PHPUnit\Rector\MethodCall\AssertTrueFalseToSpecificMethodRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(AssertNotOperatorRector::class);
    $rectorConfig->rule(AssertComparisonToSpecificMethodRector::class);
    $rectorConfig->rule(AssertTrueFalseToSpecificMethodRector::class);
    $rectorConfig->rule(AssertSameBoolNullToSpecificMethodRector::class);
    $rectorConfig->rule(AssertFalseStrposToContainsRector::class);
    $rectorConfig->rule(AssertTrueFalseInternalTypeToSpecificMethodRector::class);
    $rectorConfig->rule(AssertCompareToSpecificMethodRector::class);
    $rectorConfig->rule(AssertIssetToSpecificMethodRector::class);
    $rectorConfig->rule(AssertInstanceOfComparisonRector::class);
    $rectorConfig->rule(AssertPropertyExistsRector::class);
    $rectorConfig->rule(AssertRegExpRector::class);
    $rectorConfig->rule(SimplifyForeachInstanceOfRector::class);
};
