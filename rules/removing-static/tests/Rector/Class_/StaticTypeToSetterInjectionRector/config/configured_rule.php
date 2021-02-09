<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\RemovingStatic\Rector\Class_\StaticTypeToSetterInjectionRector::class)->call('configure', [[
        \Rector\RemovingStatic\Rector\Class_\StaticTypeToSetterInjectionRector::STATIC_TYPES => [
            \Rector\RemovingStatic\Tests\Rector\Class_\StaticTypeToSetterInjectionRector\Source\GenericEntityFactory::class,
            // with adding a parent interface to the class
            'ParentSetterEnforcingInterface' => \Rector\RemovingStatic\Tests\Rector\Class_\StaticTypeToSetterInjectionRector\Source\GenericEntityFactoryWithInterface::class,
        ],
    ]]);
};
