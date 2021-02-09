<?php

use Rector\Autodiscovery\Rector\FileNode\MoveValueObjectsToValueObjectDirectoryRector;
use Rector\Autodiscovery\Tests\Rector\FileNode\MoveValueObjectsToValueObjectDirectoryRector\Source\ObviousValueObjectInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(MoveValueObjectsToValueObjectDirectoryRector::class)->call(
        'configure',
        [[
            MoveValueObjectsToValueObjectDirectoryRector::TYPES => [ObviousValueObjectInterface::class],
        ]]
    );
    $services->set(MoveValueObjectsToValueObjectDirectoryRector::class)->call(
        'configure',
        [[
            MoveValueObjectsToValueObjectDirectoryRector::TYPES => [ObviousValueObjectInterface::class],
        ]]
    )->call('configure', [[
        MoveValueObjectsToValueObjectDirectoryRector::SUFFIXES => ['Search'],
    ]]);
    $services->set(MoveValueObjectsToValueObjectDirectoryRector::class)->call(
        'configure',
        [[
            MoveValueObjectsToValueObjectDirectoryRector::TYPES => [ObviousValueObjectInterface::class],
        ]]
    )->call('configure', [[
        MoveValueObjectsToValueObjectDirectoryRector::SUFFIXES => ['Search'],
    ]])->call('configure', [[
        MoveValueObjectsToValueObjectDirectoryRector::ENABLE_VALUE_OBJECT_GUESSING => true,
    ]]);
};
