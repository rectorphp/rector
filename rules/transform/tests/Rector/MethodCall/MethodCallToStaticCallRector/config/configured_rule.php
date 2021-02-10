<?php

use Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector;
use Rector\Transform\Tests\Rector\MethodCall\MethodCallToStaticCallRector\Source\AnotherDependency;
use Rector\Transform\ValueObject\MethodCallToStaticCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(MethodCallToStaticCallRector::class)->call('configure', [[
        MethodCallToStaticCallRector::METHOD_CALLS_TO_STATIC_CALLS => ValueObjectInliner::inline([

            new MethodCallToStaticCall(AnotherDependency::class, 'process', 'StaticCaller', 'anotherMethod'),

        ]),
    ]]);
};
