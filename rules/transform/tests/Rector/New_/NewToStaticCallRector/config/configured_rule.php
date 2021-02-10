<?php

use Rector\Transform\Rector\New_\NewToStaticCallRector;
use Rector\Transform\Tests\Rector\New_\NewToStaticCallRector\Source\FromNewClass;
use Rector\Transform\Tests\Rector\New_\NewToStaticCallRector\Source\IntoStaticClass;
use Rector\Transform\ValueObject\NewToStaticCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(NewToStaticCallRector::class)->call('configure', [[
        NewToStaticCallRector::TYPE_TO_STATIC_CALLS => ValueObjectInliner::inline([

            new NewToStaticCall(FromNewClass::class, IntoStaticClass::class, 'run'),

        ]),
    ]]);
};
