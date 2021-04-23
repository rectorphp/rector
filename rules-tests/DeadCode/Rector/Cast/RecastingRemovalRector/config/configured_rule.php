<?php

declare(strict_types=1);

use Rector\DeadCode\Rector\Cast\RecastingRemovalRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RecastingRemovalRector::class);
};
