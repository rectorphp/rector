<?php

declare(strict_types=1);

use Rector\Composer\Rector\ReplacePackageAndVersionComposerRector;
use Rector\Composer\ValueObject\ReplacePackageAndVersion;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ReplacePackageAndVersionComposerRector::class)
        ->call('configure', [[
            ReplacePackageAndVersionComposerRector::REPLACE_PACKAGES_AND_VERSIONS => ValueObjectInliner::inline([
                new ReplacePackageAndVersion('vendor1/package1', 'vendor1/package3', '^4.0'),
            ]),
        ]]);
};
