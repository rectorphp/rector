<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\TryCatch\RemoveDeadTryCatchRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RemoveDeadTryCatchRector::class);
};
