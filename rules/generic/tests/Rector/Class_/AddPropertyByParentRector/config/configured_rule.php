<?php

use Rector\Generic\Rector\Class_\AddPropertyByParentRector;
use Rector\Generic\Tests\Rector\Class_\AddPropertyByParentRector\Source\SomeParentClassToAddDependencyBy;
use Rector\Generic\ValueObject\AddPropertyByParent;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(AddPropertyByParentRector::class)->call('configure', [[
        AddPropertyByParentRector::PARENT_DEPENDENCIES => ValueObjectInliner::inline([

            new AddPropertyByParent(SomeParentClassToAddDependencyBy::class, 'SomeDependency'),

        ]),
    ]]);
};
