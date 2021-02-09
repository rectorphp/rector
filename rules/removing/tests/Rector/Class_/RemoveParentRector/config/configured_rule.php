<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Removing\Rector\Class_\RemoveParentRector::class)->call('configure', [[
        \Rector\Removing\Rector\Class_\RemoveParentRector::PARENT_TYPES_TO_REMOVE => [
            \Rector\Removing\Tests\Rector\Class_\RemoveParentRector\Source\ParentTypeToBeRemoved::class,
        ],
    ]]);
};
