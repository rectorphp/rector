<?php

declare(strict_types=1);

use Rector\SOLID\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector;
use Rector\SOLID\Rector\If_\ChangeNestedIfsToEarlyReturnRector;
use Rector\SOLID\Rector\If_\RemoveAlwaysElseRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveAlwaysElseRector::class);

    $services->set(ChangeNestedIfsToEarlyReturnRector::class);

    $services->set(ChangeIfElseValueAssignToEarlyReturnRector::class);
};
