<?php

use Rector\Transform\Rector\New_\NewToMethodCallRector;
use Rector\Transform\Tests\Rector\New_\NewToMethodCallRector\Source\MyClass;
use Rector\Transform\Tests\Rector\New_\NewToMethodCallRector\Source\MyClassFactory;
use Rector\Transform\ValueObject\NewToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(NewToMethodCallRector::class)->call('configure', [[
        NewToMethodCallRector::NEWS_TO_METHOD_CALLS => ValueObjectInliner::inline([

            new NewToMethodCall(MyClass::class, MyClassFactory::class, 'create'),

        ]),
    ]]);
};
