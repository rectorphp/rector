<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Ternary\SwitchNegatedTernaryRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\If_\RemoveDeadInstanceOfRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RemoveDeadInstanceOfRector::class);
    $rectorConfig->rule(SwitchNegatedTernaryRector::class);
};
