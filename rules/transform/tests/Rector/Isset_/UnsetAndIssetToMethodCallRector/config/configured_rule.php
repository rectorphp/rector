<?php

use Rector\Transform\Rector\Isset_\UnsetAndIssetToMethodCallRector;
use Rector\Transform\Tests\Rector\Isset_\UnsetAndIssetToMethodCallRector\Source\LocalContainer;
use Rector\Transform\ValueObject\UnsetAndIssetToMethodCall;

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(UnsetAndIssetToMethodCallRector::class)->call('configure', [[
        UnsetAndIssetToMethodCallRector::ISSET_UNSET_TO_METHOD_CALL => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            

            new UnsetAndIssetToMethodCall(LocalContainer::class, 'hasService', 'removeService'),

            
        ]),
    ]]);
};
