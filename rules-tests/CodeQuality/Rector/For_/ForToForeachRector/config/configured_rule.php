<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\For_\ForToForeachRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ForToForeachRector::class);
};
