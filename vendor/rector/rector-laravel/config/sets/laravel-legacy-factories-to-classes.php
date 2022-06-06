<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Laravel\Rector\FuncCall\FactoryFuncCallToStaticCallRector;
use RectorPrefix20220606\Rector\Laravel\Rector\MethodCall\FactoryApplyingStatesRector;
use RectorPrefix20220606\Rector\Laravel\Rector\Namespace_\FactoryDefinitionRector;
return static function (RectorConfig $rectorConfig) : void {
    // https://laravel.com/docs/7.x/database-testing#writing-factories
    // https://laravel.com/docs/8.x/database-testing#defining-model-factories
    $rectorConfig->rule(FactoryDefinitionRector::class);
    // https://laravel.com/docs/7.x/database-testing#using-factories
    // https://laravel.com/docs/8.x/database-testing#creating-models-using-factories
    $rectorConfig->rule(FactoryApplyingStatesRector::class);
    $rectorConfig->rule(FactoryFuncCallToStaticCallRector::class);
};
