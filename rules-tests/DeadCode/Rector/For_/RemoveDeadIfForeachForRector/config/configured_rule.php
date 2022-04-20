<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\For_\RemoveDeadIfForeachForRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RemoveDeadIfForeachForRector::class);
};
