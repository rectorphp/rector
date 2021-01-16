<?php

declare(strict_types=1);

use Rector\Composer\Rector\AddPackageToRequireRector;
use Rector\Composer\ValueObject\PackageAndVersion;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddPackageToRequireRector::class)
        ->call('configure', [[
            AddPackageToRequireRector::PACKAGES_AND_VERSIONS => ValueObjectInliner::inline([
                new PackageAndVersion('vendor1/package3', '^3.0'),
            ]),
        ]]);
};
