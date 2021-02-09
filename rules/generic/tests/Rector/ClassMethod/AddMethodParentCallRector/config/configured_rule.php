<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Generic\Rector\ClassMethod\AddMethodParentCallRector::class)->call('configure', [[
        \Rector\Generic\Rector\ClassMethod\AddMethodParentCallRector::METHODS_BY_PARENT_TYPES => [
            \Rector\Generic\Tests\Rector\ClassMethod\AddMethodParentCallRector\Source\ParentClassWithNewConstructor::class => '__construct',
        ],
    ]]);
};
