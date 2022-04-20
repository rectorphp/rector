<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\If_\RemoveDeadInstanceOfRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(EncapsedStringsToSprintfRector::class);
    $rectorConfig->rule(RemoveDeadInstanceOfRector::class);
};
