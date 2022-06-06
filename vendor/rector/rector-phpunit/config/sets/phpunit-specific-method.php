<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\PHPUnit\Rector\Foreach_\SimplifyForeachInstanceOfRector;
use RectorPrefix20220606\Rector\PHPUnit\Rector\MethodCall\AssertCompareToSpecificMethodRector;
use RectorPrefix20220606\Rector\PHPUnit\Rector\MethodCall\AssertComparisonToSpecificMethodRector;
use RectorPrefix20220606\Rector\PHPUnit\Rector\MethodCall\AssertFalseStrposToContainsRector;
use RectorPrefix20220606\Rector\PHPUnit\Rector\MethodCall\AssertInstanceOfComparisonRector;
use RectorPrefix20220606\Rector\PHPUnit\Rector\MethodCall\AssertIssetToSpecificMethodRector;
use RectorPrefix20220606\Rector\PHPUnit\Rector\MethodCall\AssertNotOperatorRector;
use RectorPrefix20220606\Rector\PHPUnit\Rector\MethodCall\AssertPropertyExistsRector;
use RectorPrefix20220606\Rector\PHPUnit\Rector\MethodCall\AssertRegExpRector;
use RectorPrefix20220606\Rector\PHPUnit\Rector\MethodCall\AssertSameBoolNullToSpecificMethodRector;
use RectorPrefix20220606\Rector\PHPUnit\Rector\MethodCall\AssertTrueFalseInternalTypeToSpecificMethodRector;
use RectorPrefix20220606\Rector\PHPUnit\Rector\MethodCall\AssertTrueFalseToSpecificMethodRector;
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
