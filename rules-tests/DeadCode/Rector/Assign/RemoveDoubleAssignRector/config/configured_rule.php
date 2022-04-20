<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Assign\RemoveDoubleAssignRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RemoveDoubleAssignRector::class);
};
