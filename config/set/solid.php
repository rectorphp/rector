<?php

declare(strict_types=1);

use Rector\SOLID\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector;
use Rector\SOLID\Rector\Class_\RepeatedLiteralToClassConstantRector;
use Rector\SOLID\Rector\Variable\MoveVariableDeclarationNearReferenceRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangeReadOnlyVariableWithDefaultValueToConstantRector::class);
    $services->set(RepeatedLiteralToClassConstantRector::class);
    $services->set(MoveVariableDeclarationNearReferenceRector::class);
};
