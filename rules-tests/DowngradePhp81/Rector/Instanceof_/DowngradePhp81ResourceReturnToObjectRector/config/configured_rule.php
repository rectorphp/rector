<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp81\Rector\Instanceof_\DowngradePhp81ResourceReturnToObjectRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(DowngradePhp81ResourceReturnToObjectRector::class);
};
