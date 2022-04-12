<?php

declare(strict_types=1);

use Rector\Composer\Rector\ChangePackageVersionComposerRector;
use Rector\Composer\Rector\ReplacePackageAndVersionComposerRector;
use Rector\Composer\ValueObject\PackageAndVersion;
use Rector\Composer\ValueObject\ReplacePackageAndVersion;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(ReplacePackageAndVersionComposerRector::class)
        ->configure([new ReplacePackageAndVersion('vendor1/package2', 'vendor2/package1', '^3.0')]);

    $services->set(ChangePackageVersionComposerRector::class)
        ->configure([new PackageAndVersion('vendor1/package3', '~3.0.0')]);
};
