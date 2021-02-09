<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Generic\Rector\ClassMethod\NormalToFluentRector::class)->call('configure', [[
        \Rector\Generic\Rector\ClassMethod\NormalToFluentRector::CALLS_TO_FLUENT => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            

















            new \Rector\Generic\ValueObject\NormalToFluent(
                \Rector\Generic\Tests\Rector\ClassMethod\NormalToFluentRector\Source\FluentInterfaceClass::class,
                ['someFunction', 'otherFunction', 'joinThisAsWell']),
        ]
        ),
    ]]);
};
