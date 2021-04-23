<?php

declare(strict_types=1);

use Rector\Tests\Transform\Rector\Assign\GetAndSetToMethodCallRector\Source\Klarka;
use Rector\Tests\Transform\Rector\Assign\GetAndSetToMethodCallRector\Source\SomeContainer;
use Rector\Transform\Rector\Assign\GetAndSetToMethodCallRector;
use Rector\Transform\ValueObject\GetAndSetToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(GetAndSetToMethodCallRector::class)
        ->call('configure', [[
            GetAndSetToMethodCallRector::TYPE_TO_METHOD_CALLS => ValueObjectInliner::inline([
                new GetAndSetToMethodCall(SomeContainer::class, 'getService', 'addService'),
                new GetAndSetToMethodCall(Klarka::class, 'get', 'set'),
            ]),
        ]]);
};
