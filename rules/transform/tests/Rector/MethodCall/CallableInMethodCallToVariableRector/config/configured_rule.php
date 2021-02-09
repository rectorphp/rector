<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\MethodCall\CallableInMethodCallToVariableRector::class)->call(
        'configure',
        [[
            \Rector\Transform\Rector\MethodCall\CallableInMethodCallToVariableRector::CALLABLE_IN_METHOD_CALL_TO_VARIABLE => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
                
























                new \Rector\Transform\ValueObject\CallableInMethodCallToVariable(
                    \Rector\Transform\Tests\Rector\MethodCall\CallableInMethodCallToVariableRector\Source\DummyCache::class,
                    'save',
                    1
                ),
























                
            ]),
        ]]
    );
};
