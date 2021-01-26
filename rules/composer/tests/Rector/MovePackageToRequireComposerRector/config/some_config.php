<?php

declare(strict_types=1);

use Rector\Composer\Rector\MovePackageToRequireComposerRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MovePackageToRequireComposerRector::class)
        ->call('configure', [[
            MovePackageToRequireComposerRector::PACKAGE_NAMES => ['vendor1/package1'],
        ]]);
};
