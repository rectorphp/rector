<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector;
use Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(RemoveEmptyClassMethodRector::class);
    $services->set(RemoveDeadZeroAndOneOperationRector::class);
};
