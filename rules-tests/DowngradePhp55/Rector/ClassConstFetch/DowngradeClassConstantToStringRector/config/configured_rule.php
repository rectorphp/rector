<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp55\Rector\ClassConstFetch\DowngradeClassConstantToStringRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(DowngradeClassConstantToStringRector::class);
};
