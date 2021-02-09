<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\New_\NewToMethodCallRector::class)->call('configure', [[
        \Rector\Transform\Rector\New_\NewToMethodCallRector::NEWS_TO_METHOD_CALLS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            

















            new \Rector\Transform\ValueObject\NewToMethodCall(
                \Rector\Transform\Tests\Rector\New_\NewToMethodCallRector\Source\MyClass::class,
                \Rector\Transform\Tests\Rector\New_\NewToMethodCallRector\Source\MyClassFactory::class,
                'create'
            ),
























            
        ]),
    ]]);
};
