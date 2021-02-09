<?php

use Rector\Generic\Rector\Class_\AddInterfaceByTraitRector;
use Rector\Generic\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeInterface;
use Rector\Generic\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(AddInterfaceByTraitRector::class)->call('configure', [[
        AddInterfaceByTraitRector::INTERFACE_BY_TRAIT => [
            SomeTrait::class => SomeInterface::class,
        ],
    ]]);
};
