<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Plus\UseIncrementAssignRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(UseIncrementAssignRector::class);
};
