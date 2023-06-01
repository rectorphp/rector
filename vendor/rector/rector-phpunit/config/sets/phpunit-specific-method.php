<?php

declare (strict_types=1);
namespace RectorPrefix202306;

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
    $rectorConfig->rules([AssertNotOperatorRector::class, AssertComparisonToSpecificMethodRector::class, AssertTrueFalseToSpecificMethodRector::class, AssertSameBoolNullToSpecificMethodRector::class, AssertFalseStrposToContainsRector::class, AssertTrueFalseInternalTypeToSpecificMethodRector::class, AssertCompareToSpecificMethodRector::class, AssertIssetToSpecificMethodRector::class, AssertInstanceOfComparisonRector::class, AssertPropertyExistsRector::class, AssertRegExpRector::class, SimplifyForeachInstanceOfRector::class]);
};
