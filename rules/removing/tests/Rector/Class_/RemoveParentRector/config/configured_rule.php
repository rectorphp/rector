<?php

use Rector\Removing\Rector\Class_\RemoveParentRector;
use Rector\Removing\Tests\Rector\Class_\RemoveParentRector\Source\ParentTypeToBeRemoved;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RemoveParentRector::class)->call('configure', [[
        RemoveParentRector::PARENT_TYPES_TO_REMOVE => [ParentTypeToBeRemoved::class],
    ]]);
};
