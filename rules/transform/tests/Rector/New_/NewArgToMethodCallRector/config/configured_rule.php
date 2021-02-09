<?php

use Rector\Transform\Rector\New_\NewArgToMethodCallRector;
use Rector\Transform\Tests\Rector\New_\NewArgToMethodCallRector\Source\SomeDotenv;
use Rector\Transform\ValueObject\NewArgToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(NewArgToMethodCallRector::class)->call('configure', [[
        NewArgToMethodCallRector::NEW_ARGS_TO_METHOD_CALLS => ValueObjectInliner::inline([
            new NewArgToMethodCall(SomeDotenv::class, true, 'usePutenv'),
        ]),
    ]]);
};
