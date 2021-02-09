<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Generic\Rector\Class_\AddInterfaceByTraitRector::class)->call('configure', [[
        \Rector\Generic\Rector\Class_\AddInterfaceByTraitRector::INTERFACE_BY_TRAIT => [
            \Rector\Generic\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeTrait::class => \Rector\Generic\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeInterface::class,
        ],
    ]]);
};
