<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DowngradeArraySpreadRector::class);
};
