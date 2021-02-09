<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(
        # 1. first convert mocks
        \Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecMocksToPHPUnitMocksRector::class
    );
    $services->set(\Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecPromisesToPHPUnitAssertRector::class);
    $services->set(\Rector\PhpSpecToPHPUnit\Rector\ClassMethod\PhpSpecMethodToPHPUnitMethodRector::class);
    $services->set(\Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecClassToPHPUnitClassRector::class);
    $services->set(\Rector\PhpSpecToPHPUnit\Rector\Class_\AddMockPropertiesRector::class);
    $services->set(\Rector\PhpSpecToPHPUnit\Rector\Variable\MockVariableToPropertyFetchRector::class);
};
