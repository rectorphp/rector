<?php

declare(strict_types=1);

use Rector\Tests\Transform\Rector\Class_\AddInterfaceByParentRector\Source\SomeInterface;
use Rector\Tests\Transform\Rector\Class_\AddInterfaceByParentRector\Source\SomeParent;
use Rector\Transform\Rector\Class_\AddInterfaceByParentRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(AddInterfaceByParentRector::class)
        ->configure([
            SomeParent::class => SomeInterface::class,
        ]);
};
