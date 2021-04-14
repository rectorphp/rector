<?php

declare(strict_types=1);

use Rector\LeagueEvent\Rector\MethodCall\DispatchStringToObjectRector;
use Rector\Transform\Rector\Class_\AddInterfaceByParentRector;
use Rector\Transform\Rector\Class_\AddInterfaceByTraitRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DispatchStringToObjectRector::class);

    $services->set(AddInterfaceByTraitRector::class)
        ->call('configure', [[
            AddInterfaceByTraitRector::INTERFACE_BY_TRAIT => [
                'SomeTrait' => 'SomeInterface',
            ],
        ]]);
};
