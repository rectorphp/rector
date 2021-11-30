<?php

declare(strict_types=1);

use Rector\Tests\Transform\Rector\Assign\DimFetchAssignToMethodCallRector\Source\SomeRoute;
use Rector\Tests\Transform\Rector\Assign\DimFetchAssignToMethodCallRector\Source\SomeRouteList;
use Rector\Transform\Rector\Assign\DimFetchAssignToMethodCallRector;
use Rector\Transform\ValueObject\DimFetchAssignToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DimFetchAssignToMethodCallRector::class)
        ->configure([new DimFetchAssignToMethodCall(SomeRouteList::class, SomeRoute::class, 'addRoute')]);
};
