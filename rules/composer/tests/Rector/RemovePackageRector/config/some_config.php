<?php

declare(strict_types=1);

use Rector\Composer\Rector\RemovePackageRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemovePackageRector::class)
        ->call('configure', [[
            RemovePackageRector::PACKAGE_NAMES => ['vendor1/package3', 'vendor1/package1', 'vendor1/package2'],
        ]]);
};
