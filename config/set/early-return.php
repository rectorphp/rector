<?php

declare (strict_types=1);
namespace RectorPrefix202608;

use Rector\Config\RectorConfig;
use Rector\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector;
use Rector\EarlyReturn\Rector\If_\ChangeNestedIfsToEarlyReturnRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([ChangeNestedForeachIfsToEarlyContinueRector::class, ChangeNestedIfsToEarlyReturnRector::class]);
};
