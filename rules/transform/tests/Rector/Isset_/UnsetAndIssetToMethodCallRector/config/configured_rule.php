<?php

use Rector\Transform\Rector\Isset_\UnsetAndIssetToMethodCallRector;
use Rector\Transform\Tests\Rector\Isset_\UnsetAndIssetToMethodCallRector\Source\LocalContainer;
use Rector\Transform\ValueObject\UnsetAndIssetToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(UnsetAndIssetToMethodCallRector::class)->call('configure', [[
        UnsetAndIssetToMethodCallRector::ISSET_UNSET_TO_METHOD_CALL => ValueObjectInliner::inline([

            new UnsetAndIssetToMethodCall(LocalContainer::class, 'hasService', 'removeService'),

        ]),
    ]]);
};
