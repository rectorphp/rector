<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use Rector\Carbon\Rector\MethodCall\ChangeCarbonSingularMethodCallToPluralRector;
use Rector\Carbon\Rector\MethodCall\ChangeDiffForHumansArgsRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
# source: https://carbon.nesbot.com/docs/#api-carbon-2
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Carbon\Rector\MethodCall\ChangeDiffForHumansArgsRector::class);
    $services->set(\Rector\Carbon\Rector\MethodCall\ChangeCarbonSingularMethodCallToPluralRector::class);
};
