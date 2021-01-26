<?php

declare(strict_types=1);

use Rector\Composer\Rector\AddPackageToRequireDevComposerRector;
use Rector\Composer\ValueObject\PackageAndVersion;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddPackageToRequireDevComposerRector::class)
        ->call('configure', [[
            AddPackageToRequireDevComposerRector::PACKAGES_AND_VERSIONS => ValueObjectInliner::inline([
                new PackageAndVersion('vendor1/package3', '^3.0'),
                new PackageAndVersion('vendor1/package1', '^3.0'),
                new PackageAndVersion('vendor1/package2', '^3.0'),
            ]),
        ]]);
};
