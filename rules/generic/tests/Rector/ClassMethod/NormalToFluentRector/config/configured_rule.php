<?php

use Rector\Generic\Rector\ClassMethod\NormalToFluentRector;
use Rector\Generic\Tests\Rector\ClassMethod\NormalToFluentRector\Source\FluentInterfaceClass;
use Rector\Generic\ValueObject\NormalToFluent;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(NormalToFluentRector::class)->call('configure', [[
        NormalToFluentRector::CALLS_TO_FLUENT => ValueObjectInliner::inline([

            new NormalToFluent(FluentInterfaceClass::class, ['someFunction', 'otherFunction', 'joinThisAsWell']),
        ]
        ),
    ]]);
};
