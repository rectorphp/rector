<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector;
use RectorPrefix20220606\Rector\EarlyReturn\Rector\Foreach_\ReturnAfterToEarlyOnBreakRector;
use RectorPrefix20220606\Rector\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector;
use RectorPrefix20220606\Rector\EarlyReturn\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector;
use RectorPrefix20220606\Rector\EarlyReturn\Rector\If_\ChangeNestedIfsToEarlyReturnRector;
use RectorPrefix20220606\Rector\EarlyReturn\Rector\If_\ChangeOrIfContinueToMultiContinueRector;
use RectorPrefix20220606\Rector\EarlyReturn\Rector\If_\ChangeOrIfReturnToEarlyReturnRector;
use RectorPrefix20220606\Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use RectorPrefix20220606\Rector\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector;
use RectorPrefix20220606\Rector\EarlyReturn\Rector\Return_\ReturnBinaryAndToEarlyReturnRector;
use RectorPrefix20220606\Rector\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector;
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
