<?php

declare(strict_types=1);

use Rector\Composer\Rector\ChangePackageVersionComposerRector;
use Rector\Composer\ValueObject\PackageAndVersion;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(ChangePackageVersionComposerRector::class)
        ->configure([new PackageAndVersion('vendor1/package3', '^15.0')]);
};
