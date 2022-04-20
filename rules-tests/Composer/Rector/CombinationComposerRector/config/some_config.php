<?php

declare(strict_types=1);

use Rector\Composer\Rector\ChangePackageVersionComposerRector;
use Rector\Composer\Rector\ReplacePackageAndVersionComposerRector;
use Rector\Composer\ValueObject\PackageAndVersion;
use Rector\Composer\ValueObject\ReplacePackageAndVersion;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(
            ReplacePackageAndVersionComposerRector::class,
            [new ReplacePackageAndVersion('vendor1/package2', 'vendor2/package1', '^3.0')]
        );

    $rectorConfig
        ->ruleWithConfiguration(
            ChangePackageVersionComposerRector::class,
            [new PackageAndVersion('vendor1/package3', '~3.0.0')]
        );
};
