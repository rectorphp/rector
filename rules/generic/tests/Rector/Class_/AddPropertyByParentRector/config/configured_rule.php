<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Generic\Rector\Class_\AddPropertyByParentRector::class)->call('configure', [[
        \Rector\Generic\Rector\Class_\AddPropertyByParentRector::PARENT_DEPENDENCIES => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            

















            new \Rector\Generic\ValueObject\AddPropertyByParent(
                \Rector\Generic\Tests\Rector\Class_\AddPropertyByParentRector\Source\SomeParentClassToAddDependencyBy::class,
                'SomeDependency'
            ),
























            
        ]),
    ]]);
};
