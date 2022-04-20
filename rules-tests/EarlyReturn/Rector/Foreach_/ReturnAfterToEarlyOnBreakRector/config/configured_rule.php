<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\EarlyReturn\Rector\Foreach_\ReturnAfterToEarlyOnBreakRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ReturnAfterToEarlyOnBreakRector::class);
};
