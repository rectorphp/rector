<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Switch_\BinarySwitchToIfElseRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(BinarySwitchToIfElseRector::class);
};
