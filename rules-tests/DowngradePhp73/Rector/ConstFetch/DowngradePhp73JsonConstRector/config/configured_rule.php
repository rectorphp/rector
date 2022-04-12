<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp73\Rector\ConstFetch\DowngradePhp73JsonConstRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(DowngradePhp73JsonConstRector::class);
};
