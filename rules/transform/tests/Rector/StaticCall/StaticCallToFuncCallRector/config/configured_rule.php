<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector::class)->call('configure', [[
        \Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector::STATIC_CALLS_TO_FUNCTIONS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            

















            new \Rector\Transform\ValueObject\StaticCallToFuncCall(
                \Rector\Transform\Tests\Rector\StaticCall\StaticCallToFuncCallRector\Source\SomeOldStaticClass::class,
                'render',
                'view'
            ),
























            
        ]),
    ]]);
};
