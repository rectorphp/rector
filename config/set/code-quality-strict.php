<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\If_\MoveOutMethodCallInsideIfConditionRector;
use Rector\Performance\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(MoveOutMethodCallInsideIfConditionRector::class);
    $services->set(CountArrayToEmptyArrayComparisonRector::class);
};
