<?php

declare(strict_types=1);

use Rector\Carbon\Rector\MethodCall\ChangeCarbonSingularMethodCallToPluralRector;
use Rector\Carbon\Rector\MethodCall\ChangeDiffForHumansArgsRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# source: https://carbon.nesbot.com/docs/#api-carbon-2
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ChangeDiffForHumansArgsRector::class);
    $services->set(ChangeCarbonSingularMethodCallToPluralRector::class);
};
