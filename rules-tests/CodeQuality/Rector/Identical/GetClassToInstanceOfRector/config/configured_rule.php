<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Identical\GetClassToInstanceOfRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(GetClassToInstanceOfRector::class);
};
