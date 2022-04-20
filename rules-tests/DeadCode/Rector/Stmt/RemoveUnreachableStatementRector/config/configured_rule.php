<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RemoveUnreachableStatementRector::class);
};
