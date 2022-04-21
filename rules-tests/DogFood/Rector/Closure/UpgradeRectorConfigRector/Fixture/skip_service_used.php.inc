<?php

namespace Rector\Tests\DogFood\Rector\Closure\UpgradeRectorConfigRector\Fixture;

use Rector\Config\RectorConfig;
use Rector\DogFood\Rector\Closure\UpgradeRectorConfigRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->load('Rector\\', __DIR__ . '/../rules')
        ->exclude([
            __DIR__ . '/../rules/*/ValueObject/*',
            __DIR__ . '/../rules/*/Rector/*',
            __DIR__ . '/../rules/*/Contract/*',
            __DIR__ . '/../rules/*/Exception/*',
            __DIR__ . '/../rules/*/Enum/*',
        ]);

    $services->set(UpgradeRectorConfigRector::class);
};
