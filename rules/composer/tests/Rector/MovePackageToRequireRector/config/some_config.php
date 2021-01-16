<?php

declare(strict_types=1);

use Rector\Composer\Rector\MovePackageToRequireRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MovePackageToRequireRector::class)
        ->call('configure', [[
            MovePackageToRequireRector::PACKAGE_NAMES => [
                'vendor1/package1'
            ],
        ]]);
};
