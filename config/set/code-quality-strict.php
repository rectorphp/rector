<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\Variable\MoveVariableDeclarationNearReferenceRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\CodingStyle\Rector\MethodCall\UseMessageVariableForSprintfInSymfonyStyleRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(CountArrayToEmptyArrayComparisonRector::class);
    $services->set(MoveVariableDeclarationNearReferenceRector::class);
    $services->set(UseMessageVariableForSprintfInSymfonyStyleRector::class);
    $services->set(FlipTypeControlToUseExclusiveTypeRector::class);
};
