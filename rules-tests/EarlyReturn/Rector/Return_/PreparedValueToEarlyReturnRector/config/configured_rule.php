<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(PreparedValueToEarlyReturnRector::class);
};
