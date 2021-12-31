<?php

declare (strict_types=1);
namespace RectorPrefix20211231;

use Rector\Laravel\Set\LaravelLevelSetList;
use Rector\Laravel\Set\LaravelSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(\Rector\Laravel\Set\LaravelSetList::LARAVEL_54);
    $containerConfigurator->import(\Rector\Laravel\Set\LaravelLevelSetList::UP_TO_LARAVEL_53);
};
