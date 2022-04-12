<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Plus\UseIncrementAssignRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(UseIncrementAssignRector::class);
};
