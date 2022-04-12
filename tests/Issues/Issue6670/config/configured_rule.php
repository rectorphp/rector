<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(RemoveUnusedPrivateMethodRector::class);
    $services->set(RemoveAlwaysElseRector::class);
};
