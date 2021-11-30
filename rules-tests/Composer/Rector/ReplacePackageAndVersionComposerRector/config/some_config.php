<?php

declare(strict_types=1);

use Rector\Composer\Rector\ReplacePackageAndVersionComposerRector;
use Rector\Composer\ValueObject\ReplacePackageAndVersion;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ReplacePackageAndVersionComposerRector::class)
        ->configure([new ReplacePackageAndVersion('vendor1/package1', 'vendor1/package3', '^4.0')]);
};
