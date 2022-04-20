<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp74\Rector\MethodCall\DowngradeReflectionGetTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DowngradeReflectionGetTypeRector::class);
};
