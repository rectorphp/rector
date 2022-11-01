<?php

declare (strict_types=1);
namespace RectorPrefix202211;

use Rector\Config\RectorConfig;
use Rector\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector;
use Rector\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\ChangeNestedIfsToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\ChangeOrIfContinueToMultiContinueRector;
use Rector\EarlyReturn\Rector\If_\ChangeOrIfReturnToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector;
use Rector\EarlyReturn\Rector\Return_\ReturnBinaryAndToEarlyReturnRector;
use Rector\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector;
use Rector\EarlyReturn\Rector\StmtsAwareInterface\ReturnEarlyIfVariableRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([ChangeNestedForeachIfsToEarlyContinueRector::class, ChangeAndIfToEarlyReturnRector::class, ChangeIfElseValueAssignToEarlyReturnRector::class, ChangeNestedIfsToEarlyReturnRector::class, RemoveAlwaysElseRector::class, ReturnBinaryAndToEarlyReturnRector::class, ChangeOrIfReturnToEarlyReturnRector::class, ChangeOrIfContinueToMultiContinueRector::class, PreparedValueToEarlyReturnRector::class, ReturnBinaryOrToEarlyReturnRector::class, ReturnEarlyIfVariableRector::class]);
};
