<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\Laravel\Rector\FuncCall\FactoryFuncCallToStaticCallRector;
use Rector\Laravel\Rector\MethodCall\FactoryApplyingStatesRector;
use Rector\Laravel\Rector\Namespace_\FactoryDefinitionRector;
return static function (RectorConfig $rectorConfig) : void {
    // https://laravel.com/docs/7.x/database-testing#writing-factories
    // https://laravel.com/docs/8.x/database-testing#defining-model-factories
    $rectorConfig->rule(FactoryDefinitionRector::class);
    // https://laravel.com/docs/7.x/database-testing#using-factories
    // https://laravel.com/docs/8.x/database-testing#creating-models-using-factories
    $rectorConfig->rule(FactoryApplyingStatesRector::class);
    $rectorConfig->rule(FactoryFuncCallToStaticCallRector::class);
};
