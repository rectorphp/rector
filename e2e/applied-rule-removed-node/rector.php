<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveDeadConstructorRector;
use Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
    ]);

    $rectorConfig->rule(RemoveDeadConstructorRector::class);
    $rectorConfig->rule(RemoveAlwaysTrueIfConditionRector::class);
};
