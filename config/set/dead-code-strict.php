<?php

declare(strict_types=1);

use Rector\DeadCode\Rector\Class_\RemoveEmptyAbstractClassRector;
use Rector\DeadCode\Rector\Class_\RemoveUselessJustForSakeInterfaceRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPublicMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RemoveUnusedPublicMethodRector::class);
    $services->set(RemoveUselessJustForSakeInterfaceRector::class);
    $services->set(RemoveEmptyAbstractClassRector::class);
};
