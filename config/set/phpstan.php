<?php

declare(strict_types=1);

use Rector\PHPStan\Rector\Assign\PHPStormVarAnnotationRector;
use Rector\PHPStan\Rector\Cast\RecastingRemovalRector;
use Rector\PHPStan\Rector\Node\RemoveNonExistingVarAnnotationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RecastingRemovalRector::class);

    $services->set(PHPStormVarAnnotationRector::class);

    $services->set(RemoveNonExistingVarAnnotationRector::class);
};
