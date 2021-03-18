<?php

use Rector\Tests\Transform\Rector\StaticCall\StaticCallToFuncCallRector\Source\SomeOldStaticClass;
use Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector;
use Rector\Transform\ValueObject\StaticCallToFuncCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(StaticCallToFuncCallRector::class)
        ->call('configure', [[
            StaticCallToFuncCallRector::STATIC_CALLS_TO_FUNCTIONS => ValueObjectInliner::inline([
                new StaticCallToFuncCall(SomeOldStaticClass::class, 'render', 'view'),
            ]),
        ]]);
};
