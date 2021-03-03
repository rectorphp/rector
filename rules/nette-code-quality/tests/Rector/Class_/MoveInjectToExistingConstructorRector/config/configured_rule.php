<?php

declare(strict_types=1);

use Rector\NetteCodeQuality\Rector\Class_\MoveInjectToExistingConstructorRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MoveInjectToExistingConstructorRector::class);
};
