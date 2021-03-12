<?php

declare(strict_types=1);

use Rector\Transform\Rector\FuncCall\ArgumentFuncCallToMethodCallRector;
use Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall;
use Rector\Transform\ValueObject\ArrayFuncCallToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArgumentFuncCallToMethodCallRector::class)
        ->call('configure', [[
            ArgumentFuncCallToMethodCallRector::FUNCTIONS_TO_METHOD_CALLS => ValueObjectInliner::inline([
                new ArgumentFuncCallToMethodCall('view', 'Illuminate\Contracts\View\Factory', 'make'),
                new ArgumentFuncCallToMethodCall('route', 'Illuminate\Routing\UrlGenerator', 'route'),
                new ArgumentFuncCallToMethodCall('back', 'Illuminate\Routing\Redirector', 'back', 'back'),
                new ArgumentFuncCallToMethodCall('broadcast', 'Illuminate\Contracts\Broadcasting\Factory', 'event'),
            ]),
            ArgumentFuncCallToMethodCallRector::ARRAY_FUNCTIONS_TO_METHOD_CALLS => ValueObjectInliner::inline([
                new ArrayFuncCallToMethodCall('config', 'Illuminate\Contracts\Config\Repository', 'set', 'get'),
                new ArrayFuncCallToMethodCall('session', 'Illuminate\Session\SessionManager', 'put', 'get'),
            ]),
        ]]);
};
