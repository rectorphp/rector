<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector;
use Rector\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ChangeReadOnlyVariableWithDefaultValueToConstantRector::class);
    $rectorConfig->rule(RemoveUnusedVariableAssignRector::class);
};
