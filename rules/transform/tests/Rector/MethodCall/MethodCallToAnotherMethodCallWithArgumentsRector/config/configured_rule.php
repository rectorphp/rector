<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector::class)->call(
        'configure',
        [[
            \Rector\Transform\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector::METHOD_CALL_RENAMES_WITH_ADDED_ARGUMENTS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
                
























                new \Rector\Transform\ValueObject\MethodCallToAnotherMethodCallWithArguments(
                    \Rector\Transform\Tests\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector\Source\NetteServiceDefinition::class,
                    'setInject',
                    'addTag',
                    ['inject']),
            ]
            ),
        ]]
    );
};
