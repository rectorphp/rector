<?php

declare(strict_types=1);

use Rector\PhpSpecToPHPUnit\Rector\Class_\AddMockPropertiesRector;
use Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecClassToPHPUnitClassRector;
use Rector\PhpSpecToPHPUnit\Rector\ClassMethod\PhpSpecMethodToPHPUnitMethodRector;
use Rector\PhpSpecToPHPUnit\Rector\FileNode\RenameSpecFileToTestFileRector;
use Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecMocksToPHPUnitMocksRector;
use Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecPromisesToPHPUnitAssertRector;
use Rector\PhpSpecToPHPUnit\Rector\Variable\MockVariableToPropertyFetchRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# see: https://gnugat.github.io/2015/09/23/phpunit-with-phpspec.html
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    # 1. first convert mocks
    $services->set(PhpSpecMocksToPHPUnitMocksRector::class);

    $services->set(PhpSpecPromisesToPHPUnitAssertRector::class);

    # 2. then methods
    $services->set(PhpSpecMethodToPHPUnitMethodRector::class);

    # 3. then the class itself
    $services->set(PhpSpecClassToPHPUnitClassRector::class);

    $services->set(AddMockPropertiesRector::class);

    $services->set(MockVariableToPropertyFetchRector::class);

    $services->set(RenameSpecFileToTestFileRector::class);
};
