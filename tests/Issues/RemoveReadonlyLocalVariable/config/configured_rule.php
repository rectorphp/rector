<?php

declare(strict_types=1);

use Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector;
use Rector\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ChangeReadOnlyVariableWithDefaultValueToConstantRector::class);
    $services->set(RemoveUnusedVariableAssignRector::class);
};
