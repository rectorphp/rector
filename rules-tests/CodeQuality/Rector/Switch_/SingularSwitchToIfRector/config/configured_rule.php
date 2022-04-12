<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Switch_\SingularSwitchToIfRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(SingularSwitchToIfRector::class);
};
