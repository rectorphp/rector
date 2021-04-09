<?php

declare(strict_types=1);

use Rector\Restoration\Rector\Class_\RemoveFinalFromEntityRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RemoveFinalFromEntityRector::class);
};
