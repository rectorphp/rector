<?php

declare (strict_types=1);
namespace RectorPrefix20220609;

use Rector\Config\RectorConfig;
use Rector\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector;
use Rector\EarlyReturn\Rector\Foreach_\ReturnAfterToEarlyOnBreakRector;
use Rector\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\ChangeNestedIfsToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\ChangeOrIfContinueToMultiContinueRector;
use Rector\EarlyReturn\Rector\If_\ChangeOrIfReturnToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector;
use Rector\EarlyReturn\Rector\Return_\ReturnBinaryAndToEarlyReturnRector;
use Rector\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(ChangeNestedForeachIfsToEarlyContinueRector::class);
    $rectorConfig->rule(ChangeAndIfToEarlyReturnRector::class);
    $rectorConfig->rule(ChangeIfElseValueAssignToEarlyReturnRector::class);
    $rectorConfig->rule(ChangeNestedIfsToEarlyReturnRector::class);
    $rectorConfig->rule(RemoveAlwaysElseRector::class);
    $rectorConfig->rule(ReturnBinaryAndToEarlyReturnRector::class);
    $rectorConfig->rule(ChangeOrIfReturnToEarlyReturnRector::class);
    $rectorConfig->rule(ChangeOrIfContinueToMultiContinueRector::class);
    $rectorConfig->rule(ReturnAfterToEarlyOnBreakRector::class);
    $rectorConfig->rule(PreparedValueToEarlyReturnRector::class);
    $rectorConfig->rule(ReturnBinaryOrToEarlyReturnRector::class);
};
