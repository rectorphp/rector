<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Cast\RecastingRemovalRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RecastingRemovalRector::class);
};
