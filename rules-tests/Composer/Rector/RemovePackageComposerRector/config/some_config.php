<?php

declare(strict_types=1);

use Rector\Composer\Rector\RemovePackageComposerRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(RemovePackageComposerRector::class)
        ->configure(['vendor1/package3', 'vendor1/package1', 'vendor1/package2']);
};
