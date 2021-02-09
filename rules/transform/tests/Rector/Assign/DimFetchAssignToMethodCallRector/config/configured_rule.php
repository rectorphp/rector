<?php

use Rector\Transform\Rector\Assign\DimFetchAssignToMethodCallRector;
use Rector\Transform\ValueObject\DimFetchAssignToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DimFetchAssignToMethodCallRector::class)
        ->call('configure', [[
            DimFetchAssignToMethodCallRector::DIM_FETCH_ASSIGN_TO_METHOD_CALL => ValueObjectInliner::inline([
                new DimFetchAssignToMethodCall(
                    'Nette\Application\Routers\RouteList',
                    'Nette\Application\Routers\Route',
                    'addRoute'
                ),
            ]),
        ]]);
};
