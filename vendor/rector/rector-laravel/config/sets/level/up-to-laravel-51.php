<?php

declare (strict_types=1);
namespace RectorPrefix20220209;

use Rector\Laravel\Set\LaravelSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(\Rector\Laravel\Set\LaravelSetList::LARAVEL_50);
    $containerConfigurator->import(\Rector\Laravel\Set\LaravelSetList::LARAVEL_51);
};
