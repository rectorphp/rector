<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\FuncCall\FuncCallToStaticCallRector::class)->call('configure', [[
        \Rector\Transform\Rector\FuncCall\FuncCallToStaticCallRector::FUNC_CALLS_TO_STATIC_CALLS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            

















            new \Rector\Transform\ValueObject\FuncCallToStaticCall('view', 'SomeStaticClass', 'render'),
            new \Rector\Transform\ValueObject\FuncCallToStaticCall(
                'SomeNamespaced\view',
                'AnotherStaticClass',
                'render'
            ),
























            
        ]),
    ]]);
};
