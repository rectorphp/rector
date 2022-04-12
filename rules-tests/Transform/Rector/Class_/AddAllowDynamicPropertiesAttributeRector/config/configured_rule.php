<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Transform\Rector\Class_\AddAllowDynamicPropertiesAttributeRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(AddAllowDynamicPropertiesAttributeRector::class)
        ->configure(['*\Fixture\Process\*']);
};
