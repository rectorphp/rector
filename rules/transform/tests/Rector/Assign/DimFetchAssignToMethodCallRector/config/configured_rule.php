<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\Assign\DimFetchAssignToMethodCallRector::class)->call('configure', [[
        \Rector\Transform\Rector\Assign\DimFetchAssignToMethodCallRector::DIM_FETCH_ASSIGN_TO_METHOD_CALL => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            

















            new \Rector\Transform\ValueObject\DimFetchAssignToMethodCall(
                'Nette\Application\Routers\RouteList',
                'Nette\Application\Routers\Route',
                'addRoute'
            ),
























            
        ]),
    ]]);
};
