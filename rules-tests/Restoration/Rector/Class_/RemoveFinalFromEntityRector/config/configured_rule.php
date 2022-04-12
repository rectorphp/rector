<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Restoration\Rector\Class_\RemoveFinalFromEntityRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(RemoveFinalFromEntityRector::class);
};
