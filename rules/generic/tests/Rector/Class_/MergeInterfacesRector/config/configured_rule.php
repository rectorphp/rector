<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Generic\Rector\Class_\MergeInterfacesRector::class)->call('configure', [[
        \Rector\Generic\Rector\Class_\MergeInterfacesRector::OLD_TO_NEW_INTERFACES => [
            \Rector\Generic\Tests\Rector\Class_\MergeInterfacesRector\Source\SomeOldInterface::class => \Rector\Generic\Tests\Rector\Class_\MergeInterfacesRector\Source\SomeInterface::class,
        ],
    ]]);
};
