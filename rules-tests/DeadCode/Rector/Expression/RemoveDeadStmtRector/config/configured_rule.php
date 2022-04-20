<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Expression\RemoveDeadStmtRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RemoveDeadStmtRector::class);
};
