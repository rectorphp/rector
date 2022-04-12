<?php

declare(strict_types=1);

use Rector\Compatibility\Rector\Class_\AttributeCompatibleAnnotationRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(AttributeCompatibleAnnotationRector::class);
};
