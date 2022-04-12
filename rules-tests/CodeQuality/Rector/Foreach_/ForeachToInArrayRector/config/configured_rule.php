<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Foreach_\ForeachToInArrayRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(ForeachToInArrayRector::class);
};
