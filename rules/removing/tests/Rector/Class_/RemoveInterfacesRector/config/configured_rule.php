<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Removing\Rector\Class_\RemoveInterfacesRector::class)->call('configure', [[
        \Rector\Removing\Rector\Class_\RemoveInterfacesRector::INTERFACES_TO_REMOVE => [
            \Rector\Removing\Tests\Rector\Class_\RemoveInterfacesRector\Source\SomeInterface::class,
        ],
    ]]);
};
