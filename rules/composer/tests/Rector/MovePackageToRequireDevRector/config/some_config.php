<?php

declare(strict_types=1);

use Rector\Composer\Rector\MovePackageToRequireDevRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MovePackageToRequireDevRector::class)
        ->call('configure', [[
            MovePackageToRequireDevRector::PACKAGE_NAMES => ['vendor1/package3'],
        ]]);
};
