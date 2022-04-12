<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp56\Rector\Pow\DowngradeExponentialAssignmentOperatorRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(DowngradeExponentialAssignmentOperatorRector::class);
};
