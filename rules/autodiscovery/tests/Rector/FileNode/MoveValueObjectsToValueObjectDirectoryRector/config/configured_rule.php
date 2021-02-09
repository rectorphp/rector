<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Autodiscovery\Rector\FileNode\MoveValueObjectsToValueObjectDirectoryRector::class)->call(
        'configure',
        [[
            \Rector\Autodiscovery\Rector\FileNode\MoveValueObjectsToValueObjectDirectoryRector::TYPES => [
                \Rector\Autodiscovery\Tests\Rector\FileNode\MoveValueObjectsToValueObjectDirectoryRector\Source\ObviousValueObjectInterface::class,
            ],
        ]]
    );
    $services->set(\Rector\Autodiscovery\Rector\FileNode\MoveValueObjectsToValueObjectDirectoryRector::class)->call(
        'configure',
        [[
            \Rector\Autodiscovery\Rector\FileNode\MoveValueObjectsToValueObjectDirectoryRector::TYPES => [
                \Rector\Autodiscovery\Tests\Rector\FileNode\MoveValueObjectsToValueObjectDirectoryRector\Source\ObviousValueObjectInterface::class,
            ],
        ]]
    )->call('configure', [[
        \Rector\Autodiscovery\Rector\FileNode\MoveValueObjectsToValueObjectDirectoryRector::SUFFIXES => ['Search'],
    ]]);
    $services->set(\Rector\Autodiscovery\Rector\FileNode\MoveValueObjectsToValueObjectDirectoryRector::class)->call(
        'configure',
        [[
            \Rector\Autodiscovery\Rector\FileNode\MoveValueObjectsToValueObjectDirectoryRector::TYPES => [
                \Rector\Autodiscovery\Tests\Rector\FileNode\MoveValueObjectsToValueObjectDirectoryRector\Source\ObviousValueObjectInterface::class,
            ],
        ]]
    )->call('configure', [[
        \Rector\Autodiscovery\Rector\FileNode\MoveValueObjectsToValueObjectDirectoryRector::SUFFIXES => ['Search'],
    ]])->call('configure', [[
        \Rector\Autodiscovery\Rector\FileNode\MoveValueObjectsToValueObjectDirectoryRector::ENABLE_VALUE_OBJECT_GUESSING => true,
    ]]);
};
