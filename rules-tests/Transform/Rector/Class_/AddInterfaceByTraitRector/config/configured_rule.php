<?php

declare(strict_types=1);

use Rector\Tests\Transform\Rector\Class_\AddInterfaceByTraitRector\Source\SomeInterface;
use Rector\Tests\Transform\Rector\Class_\AddInterfaceByTraitRector\Source\SomeTrait;
use Rector\Transform\Rector\Class_\AddInterfaceByTraitRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(AddInterfaceByTraitRector::class)
        ->configure([
            SomeTrait::class => SomeInterface::class,
        ]);
};
