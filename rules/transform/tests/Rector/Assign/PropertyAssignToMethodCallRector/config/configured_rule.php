<?php

use Rector\Transform\Rector\Assign\PropertyAssignToMethodCallRector;
use Rector\Transform\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Source\ChoiceControl;
use Rector\Transform\ValueObject\PropertyAssignToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PropertyAssignToMethodCallRector::class)
        ->call('configure', [[
            PropertyAssignToMethodCallRector::PROPERTY_ASSIGNS_TO_METHODS_CALLS => ValueObjectInliner::inline([
                new PropertyAssignToMethodCall(ChoiceControl::class, 'checkAllowedValues', 'checkDefaultValue'),
            ]),
        ]]);
};
