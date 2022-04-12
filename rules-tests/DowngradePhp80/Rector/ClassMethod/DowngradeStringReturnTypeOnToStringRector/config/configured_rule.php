<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp80\Rector\ClassMethod\DowngradeStringReturnTypeOnToStringRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(DowngradeStringReturnTypeOnToStringRector::class);
};
