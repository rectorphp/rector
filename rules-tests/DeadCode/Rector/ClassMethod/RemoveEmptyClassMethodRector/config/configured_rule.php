<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RemoveEmptyClassMethodRector::class);
};
