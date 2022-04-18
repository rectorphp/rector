<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Config\RectorConfig;
use Rector\Laravel\Rector\FuncCall\FactoryFuncCallToStaticCallRector;
use Rector\Laravel\Rector\MethodCall\FactoryApplyingStatesRector;
use Rector\Laravel\Rector\Namespace_\FactoryDefinitionRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $services = $rectorConfig->services();
    // https://laravel.com/docs/7.x/database-testing#writing-factories
    // https://laravel.com/docs/8.x/database-testing#defining-model-factories
    $services->set(\Rector\Laravel\Rector\Namespace_\FactoryDefinitionRector::class);
    // https://laravel.com/docs/7.x/database-testing#using-factories
    // https://laravel.com/docs/8.x/database-testing#creating-models-using-factories
    $services->set(\Rector\Laravel\Rector\MethodCall\FactoryApplyingStatesRector::class);
    $services->set(\Rector\Laravel\Rector\FuncCall\FactoryFuncCallToStaticCallRector::class);
};
