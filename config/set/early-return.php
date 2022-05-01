<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

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
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector::class);
    $rectorConfig->rule(\Rector\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector::class);
    $rectorConfig->rule(\Rector\EarlyReturn\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector::class);
    $rectorConfig->rule(\Rector\EarlyReturn\Rector\If_\ChangeNestedIfsToEarlyReturnRector::class);
    $rectorConfig->rule(\Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector::class);
    $rectorConfig->rule(\Rector\EarlyReturn\Rector\Return_\ReturnBinaryAndToEarlyReturnRector::class);
    $rectorConfig->rule(\Rector\EarlyReturn\Rector\If_\ChangeOrIfReturnToEarlyReturnRector::class);
    $rectorConfig->rule(\Rector\EarlyReturn\Rector\If_\ChangeOrIfContinueToMultiContinueRector::class);
    $rectorConfig->rule(\Rector\EarlyReturn\Rector\Foreach_\ReturnAfterToEarlyOnBreakRector::class);
    $rectorConfig->rule(\Rector\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector::class);
    $rectorConfig->rule(\Rector\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector::class);
};
