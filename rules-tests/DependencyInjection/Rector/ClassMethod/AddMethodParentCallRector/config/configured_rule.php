<?php

use Rector\DependencyInjection\Rector\ClassMethod\AddMethodParentCallRector;
use Rector\Tests\DependencyInjection\Rector\ClassMethod\AddMethodParentCallRector\Source\ParentClassWithNewConstructor;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(AddMethodParentCallRector::class)
        ->call('configure', [[
            AddMethodParentCallRector::METHODS_BY_PARENT_TYPES => [
                ParentClassWithNewConstructor::class => '__construct',
            ],
        ]]);
};
