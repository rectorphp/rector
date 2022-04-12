<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp56\Rector\Pow\DowngradeExponentialOperatorRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(DowngradeExponentialOperatorRector::class);
};
