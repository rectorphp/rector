<?php

declare(strict_types=1);

use Rector\CodeQualityStrict\Rector\If_\MoveOutMethodCallInsideIfConditionRector;
use Rector\CodeQualityStrict\Rector\Variable\MoveVariableDeclarationNearReferenceRector;
use Rector\CodingStyle\Rector\MethodCall\UseMessageVariableForSprintfInSymfonyStyleRector;
use Rector\Performance\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(MoveOutMethodCallInsideIfConditionRector::class);
    $services->set(CountArrayToEmptyArrayComparisonRector::class);
    $services->set(MoveVariableDeclarationNearReferenceRector::class);
    $services->set(UseMessageVariableForSprintfInSymfonyStyleRector::class);
};
