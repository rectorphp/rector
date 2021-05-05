<?php

use Rector\Tests\Transform\Rector\Assign\DimFetchAssignToMethodCallRector\Source\SomeRoute;
use Rector\Tests\Transform\Rector\Assign\DimFetchAssignToMethodCallRector\Source\SomeRouteList;
use Rector\Transform\Rector\Assign\DimFetchAssignToMethodCallRector;
use Rector\Transform\ValueObject\DimFetchAssignToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DimFetchAssignToMethodCallRector::class)
        ->call('configure', [[
            DimFetchAssignToMethodCallRector::DIM_FETCH_ASSIGN_TO_METHOD_CALL => ValueObjectInliner::inline([
                new DimFetchAssignToMethodCall(SomeRouteList::class, SomeRoute::class, 'addRoute'),
            ]),
        ]]);
};
