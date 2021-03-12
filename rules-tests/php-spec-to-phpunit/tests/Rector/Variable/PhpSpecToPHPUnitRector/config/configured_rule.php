<?php

use Rector\PhpSpecToPHPUnit\Rector\Class_\AddMockPropertiesRector;
use Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecClassToPHPUnitClassRector;
use Rector\PhpSpecToPHPUnit\Rector\ClassMethod\PhpSpecMethodToPHPUnitMethodRector;
use Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecMocksToPHPUnitMocksRector;
use Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecPromisesToPHPUnitAssertRector;
use Rector\PhpSpecToPHPUnit\Rector\Variable\MockVariableToPropertyFetchRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(
        # 1. first convert mocks
        PhpSpecMocksToPHPUnitMocksRector::class
    );
    $services->set(PhpSpecPromisesToPHPUnitAssertRector::class);
    $services->set(PhpSpecMethodToPHPUnitMethodRector::class);
    $services->set(PhpSpecClassToPHPUnitClassRector::class);
    $services->set(AddMockPropertiesRector::class);
    $services->set(MockVariableToPropertyFetchRector::class);
};
