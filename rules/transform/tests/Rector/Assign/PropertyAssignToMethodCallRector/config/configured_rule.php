<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\Assign\PropertyAssignToMethodCallRector::class)->call('configure', [[
        \Rector\Transform\Rector\Assign\PropertyAssignToMethodCallRector::PROPERTY_ASSIGNS_TO_METHODS_CALLS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            

















            new \Rector\Transform\ValueObject\PropertyAssignToMethodCall(
                \Rector\Transform\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Source\ChoiceControl::class,
                'checkAllowedValues',
                'checkDefaultValue'
            ),
























            
        ]),
    ]]);
};
