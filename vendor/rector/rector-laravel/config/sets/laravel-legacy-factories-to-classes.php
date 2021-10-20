<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use Rector\Laravel\Rector\FuncCall\FactoryFuncCallToStaticCallRector;
use Rector\Laravel\Rector\MethodCall\FactoryApplyingStatesRector;
use Rector\Laravel\Rector\Namespace_\FactoryDefinitionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    // https://laravel.com/docs/7.x/database-testing#writing-factories
    // https://laravel.com/docs/8.x/database-testing#defining-model-factories
    $services->set(\Rector\Laravel\Rector\Namespace_\FactoryDefinitionRector::class);
    // https://laravel.com/docs/7.x/database-testing#using-factories
    // https://laravel.com/docs/8.x/database-testing#creating-models-using-factories
    $services->set(\Rector\Laravel\Rector\MethodCall\FactoryApplyingStatesRector::class);
    $services->set(\Rector\Laravel\Rector\FuncCall\FactoryFuncCallToStaticCallRector::class);
};
