<?php

declare(strict_types=1);

use Rector\DependencyInjection\Rector\ClassMethod\AddMethodParentCallRector;
use Rector\Tests\DependencyInjection\Rector\ClassMethod\AddMethodParentCallRector\Source\ParentClassWithNewConstructor;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(AddMethodParentCallRector::class)
        ->configure([
            ParentClassWithNewConstructor::class => '__construct',
        ]);
};
