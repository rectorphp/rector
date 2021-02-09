<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector::class)->call('configure', [[
        \Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector::METHOD_CALLS_TO_STATIC_CALLS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            

















            new \Rector\Transform\ValueObject\MethodCallToStaticCall(
                \Rector\Transform\Tests\Rector\MethodCall\MethodCallToStaticCallRector\Source\AnotherDependency::class,
                'process',
                'StaticCaller',
                'anotherMethod'
            ),
























            
        ]),
    ]]);
};
