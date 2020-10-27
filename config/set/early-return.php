<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\SOLID\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector;
use Rector\SOLID\Rector\If_\ChangeAndIfToEarlyReturnRector;
use Rector\SOLID\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector;
use Rector\SOLID\Rector\If_\ChangeNestedIfsToEarlyReturnRector;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ChangeNestedForeachIfsToEarlyContinueRector::class);
    $services->set(ChangeAndIfToEarlyReturnRector::class);
    $services->set(ChangeIfElseValueAssignToEarlyReturnRector::class);
    $services->set(ChangeNestedIfsToEarlyReturnRector::class);
};
