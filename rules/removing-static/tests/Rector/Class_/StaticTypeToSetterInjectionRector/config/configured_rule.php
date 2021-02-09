<?php

use Rector\RemovingStatic\Rector\Class_\StaticTypeToSetterInjectionRector;
use Rector\RemovingStatic\Tests\Rector\Class_\StaticTypeToSetterInjectionRector\Source\GenericEntityFactory;
use Rector\RemovingStatic\Tests\Rector\Class_\StaticTypeToSetterInjectionRector\Source\GenericEntityFactoryWithInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(StaticTypeToSetterInjectionRector::class)->call('configure', [[
        StaticTypeToSetterInjectionRector::STATIC_TYPES => [
            GenericEntityFactory::class,
            // with adding a parent interface to the class
            'ParentSetterEnforcingInterface' => GenericEntityFactoryWithInterface::class,
        ],
    ]]);
};
