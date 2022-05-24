<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Assign\RemoveDoubleAssignRector;
use Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([RemoveDoubleAssignRector::class, RemoveUnusedVariableAssignRector::class]);
};
