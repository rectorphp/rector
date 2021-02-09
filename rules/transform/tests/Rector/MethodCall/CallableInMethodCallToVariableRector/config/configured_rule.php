<?php

use Rector\Transform\Rector\MethodCall\CallableInMethodCallToVariableRector;
use Rector\Transform\Tests\Rector\MethodCall\CallableInMethodCallToVariableRector\Source\DummyCache;
use Rector\Transform\ValueObject\CallableInMethodCallToVariable;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(CallableInMethodCallToVariableRector::class)->call(
        'configure',
        [[
            CallableInMethodCallToVariableRector::CALLABLE_IN_METHOD_CALL_TO_VARIABLE => ValueObjectInliner::inline([

                new CallableInMethodCallToVariable(DummyCache::class, 'save', 1),

            ]),
        ]]
    );
};
