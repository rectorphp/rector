<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ForeachItemsAssignToEmptyArrayToAssignRector::class);
};
