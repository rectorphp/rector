<?php

use Rector\Transform\Rector\Class_\AddInterfaceByTraitRector;
use Rector\Transform\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeInterface;
use Rector\Transform\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(AddInterfaceByTraitRector::class)
        ->call('configure', [[
            AddInterfaceByTraitRector::INTERFACE_BY_TRAIT => [
                SomeTrait::class => SomeInterface::class,
            ],
        ]]);
};
