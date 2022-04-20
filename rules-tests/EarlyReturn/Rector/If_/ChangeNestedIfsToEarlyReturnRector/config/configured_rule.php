<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\EarlyReturn\Rector\If_\ChangeNestedIfsToEarlyReturnRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ChangeNestedIfsToEarlyReturnRector::class);
};
