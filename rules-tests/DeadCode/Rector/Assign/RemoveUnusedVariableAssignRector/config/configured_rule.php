<?php

declare(strict_types=1);

use Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RemoveUnusedVariableAssignRector::class);
};
