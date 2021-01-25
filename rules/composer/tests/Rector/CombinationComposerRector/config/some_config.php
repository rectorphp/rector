<?php

declare(strict_types=1);

use Rector\Composer\Rector\ChangePackageVersionRector;
use Rector\Composer\Rector\MovePackageToRequireDevRector;
use Rector\Composer\Rector\ReplacePackageAndVersionRector;
use Rector\Composer\ValueObject\PackageAndVersion;
use Rector\Composer\ValueObject\ReplacePackageAndVersion;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MovePackageToRequireDevRector::class)
        ->call('configure', [[
            MovePackageToRequireDevRector::PACKAGE_NAMES => ['vendor1/package1'],
        ]]);

    $services->set(ReplacePackageAndVersionRector::class)
        ->call('configure', [[
            ReplacePackageAndVersionRector::REPLACE_PACKAGES_AND_VERSIONS => ValueObjectInliner::inline([
                new ReplacePackageAndVersion('vendor1/package2', 'vendor2/package1', '^3.0'),
            ]),
        ]]);

    $services->set(ChangePackageVersionRector::class)
        ->call('configure', [[
            ChangePackageVersionRector::PACKAGES_AND_VERSIONS => ValueObjectInliner::inline([
                new PackageAndVersion('vendor1/package3', '~3.0.0'),
            ]),
        ]]);
};
