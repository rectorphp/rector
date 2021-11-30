<?php

declare(strict_types=1);

use Rector\Tests\Transform\Rector\Assign\PropertyAssignToMethodCallRector\Source\ChoiceControl;
use Rector\Transform\Rector\Assign\PropertyAssignToMethodCallRector;
use Rector\Transform\ValueObject\PropertyAssignToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(PropertyAssignToMethodCallRector::class)
        ->configure([
            new PropertyAssignToMethodCall(ChoiceControl::class, 'checkAllowedValues', 'checkDefaultValue'),
        ]);
};
