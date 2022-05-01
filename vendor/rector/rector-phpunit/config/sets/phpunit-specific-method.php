<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

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
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\PHPUnit\Rector\MethodCall\AssertNotOperatorRector::class);
    $rectorConfig->rule(\Rector\PHPUnit\Rector\MethodCall\AssertComparisonToSpecificMethodRector::class);
    $rectorConfig->rule(\Rector\PHPUnit\Rector\MethodCall\AssertTrueFalseToSpecificMethodRector::class);
    $rectorConfig->rule(\Rector\PHPUnit\Rector\MethodCall\AssertSameBoolNullToSpecificMethodRector::class);
    $rectorConfig->rule(\Rector\PHPUnit\Rector\MethodCall\AssertFalseStrposToContainsRector::class);
    $rectorConfig->rule(\Rector\PHPUnit\Rector\MethodCall\AssertTrueFalseInternalTypeToSpecificMethodRector::class);
    $rectorConfig->rule(\Rector\PHPUnit\Rector\MethodCall\AssertCompareToSpecificMethodRector::class);
    $rectorConfig->rule(\Rector\PHPUnit\Rector\MethodCall\AssertIssetToSpecificMethodRector::class);
    $rectorConfig->rule(\Rector\PHPUnit\Rector\MethodCall\AssertInstanceOfComparisonRector::class);
    $rectorConfig->rule(\Rector\PHPUnit\Rector\MethodCall\AssertPropertyExistsRector::class);
    $rectorConfig->rule(\Rector\PHPUnit\Rector\MethodCall\AssertRegExpRector::class);
    $rectorConfig->rule(\Rector\PHPUnit\Rector\Foreach_\SimplifyForeachInstanceOfRector::class);
};
