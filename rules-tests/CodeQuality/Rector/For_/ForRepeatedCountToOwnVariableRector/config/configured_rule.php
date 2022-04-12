<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\For_\ForRepeatedCountToOwnVariableRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(ForRepeatedCountToOwnVariableRector::class);
};
