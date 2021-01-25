<?php

declare(strict_types=1);

use Rector\Composer\Rector\AddPackageToRequireDevRector;
use Rector\Composer\ValueObject\PackageAndVersion;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddPackageToRequireDevRector::class)
        ->call('configure', [[
            AddPackageToRequireDevRector::PACKAGES_AND_VERSIONS => ValueObjectInliner::inline([
                new PackageAndVersion('vendor1/package3', '^3.0'),
                new PackageAndVersion('vendor1/package1', '^3.0'),
                new PackageAndVersion('vendor1/package2', '^3.0'),
            ]),
        ]]);
};
