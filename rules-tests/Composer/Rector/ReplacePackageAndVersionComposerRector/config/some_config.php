<?php

declare(strict_types=1);

use Rector\Composer\Rector\ReplacePackageAndVersionComposerRector;
use Rector\Composer\ValueObject\ReplacePackageAndVersion;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(
            ReplacePackageAndVersionComposerRector::class,
            [new ReplacePackageAndVersion('vendor1/package1', 'vendor1/package3', '^4.0')]
        );
};
