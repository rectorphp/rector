<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp55\Rector\FuncCall\DowngradeBoolvalRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(DowngradeBoolvalRector::class);
};
