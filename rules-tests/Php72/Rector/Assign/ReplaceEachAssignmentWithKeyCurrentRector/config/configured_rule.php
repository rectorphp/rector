<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php72\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(ReplaceEachAssignmentWithKeyCurrentRector::class);
};
