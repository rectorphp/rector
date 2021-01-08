<?php

declare(strict_types=1);

use Rector\DeadCode\Rector\Class_\RemoveUnusedClassesRector;
use Rector\DeadCode\Rector\Class_\RemoveUselessJustForSakeInterfaceRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveUnusedClassesRector::class);
    $services->set(RemoveUselessJustForSakeInterfaceRector::class);
};
