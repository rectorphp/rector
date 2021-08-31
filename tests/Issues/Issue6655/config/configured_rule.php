<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\DeadCode\Rector\Assign\RemoveUnusedAssignVariableRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SimplifyIfElseToTernaryRector::class);
    $services->set(RemoveUnusedAssignVariableRector::class);
};
