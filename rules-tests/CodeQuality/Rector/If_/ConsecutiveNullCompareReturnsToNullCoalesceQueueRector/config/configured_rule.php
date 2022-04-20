<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ConsecutiveNullCompareReturnsToNullCoalesceQueueRector::class);
};
