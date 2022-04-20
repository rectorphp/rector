<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Return_\RemoveDeadConditionAboveReturnRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RemoveDeadConditionAboveReturnRector::class);
};
