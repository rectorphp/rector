<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQualityStrict\Rector\If_\MoveOutMethodCallInsideIfConditionRector;
use Rector\CodeQualityStrict\Rector\Variable\MoveVariableDeclarationNearReferenceRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\CodingStyle\Rector\MethodCall\UseMessageVariableForSprintfInSymfonyStyleRector;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(MoveOutMethodCallInsideIfConditionRector::class);
    $services->set(CountArrayToEmptyArrayComparisonRector::class);
    $services->set(MoveVariableDeclarationNearReferenceRector::class);
    $services->set(UseMessageVariableForSprintfInSymfonyStyleRector::class);
    $services->set(FlipTypeControlToUseExclusiveTypeRector::class);
};
