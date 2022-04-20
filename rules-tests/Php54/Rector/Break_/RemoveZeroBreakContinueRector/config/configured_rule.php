<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php54\Rector\Break_\RemoveZeroBreakContinueRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RemoveZeroBreakContinueRector::class);
};
