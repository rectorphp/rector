<?php

declare(strict_types=1);

use Rector\Composer\Rector\ChangePackageVersionRector;
use Rector\Composer\ValueObject\PackageAndVersion;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangePackageVersionRector::class)
        ->call('configure', [[
            ChangePackageVersionRector::PACKAGES_AND_VERSIONS => ValueObjectInliner::inline([
                new PackageAndVersion('vendor1/package3', '^15.0'),
            ]),
        ]]);
};
