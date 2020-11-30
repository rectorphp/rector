<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\MethodCall\UseMessageVariableForSprintfInSymfonyStyleRector;
use Rector\SOLID\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector;
use Rector\SOLID\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\SOLID\Rector\Class_\MakeUnusedClassesWithChildrenAbstractRector;
use Rector\SOLID\Rector\Class_\RepeatedLiteralToClassConstantRector;
use Rector\SOLID\Rector\If_\RemoveAlwaysElseRector;
use Rector\SOLID\Rector\Property\AddFalseDefaultToBoolPropertyRector;
use Rector\SOLID\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector;
use Rector\SOLID\Rector\Variable\MoveVariableDeclarationNearReferenceRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(FinalizeClassesWithoutChildrenRector::class);
    $services->set(MakeUnusedClassesWithChildrenAbstractRector::class);
    $services->set(ChangeReadOnlyPropertyWithDefaultValueToConstantRector::class);
    $services->set(ChangeReadOnlyVariableWithDefaultValueToConstantRector::class);
    $services->set(AddFalseDefaultToBoolPropertyRector::class);
    $services->set(RepeatedLiteralToClassConstantRector::class);
    $services->set(RemoveAlwaysElseRector::class);
    $services->set(UseMessageVariableForSprintfInSymfonyStyleRector::class);
    $services->set(MoveVariableDeclarationNearReferenceRector::class);
};
