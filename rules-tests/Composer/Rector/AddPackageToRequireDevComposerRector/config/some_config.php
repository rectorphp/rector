<?php

declare(strict_types=1);

use Rector\Composer\Rector\AddPackageToRequireDevComposerRector;
use Rector\Composer\ValueObject\PackageAndVersion;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(AddPackageToRequireDevComposerRector::class)
        ->configure([
            new PackageAndVersion('vendor1/package3', '^3.0'),
            new PackageAndVersion('vendor1/package1', '^3.0'),
            new PackageAndVersion('vendor1/package2', '^3.0'),
        ]);
};
