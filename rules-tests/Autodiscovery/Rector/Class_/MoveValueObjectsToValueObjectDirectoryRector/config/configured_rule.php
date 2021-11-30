<?php

declare(strict_types=1);

use Rector\Autodiscovery\Rector\Class_\MoveValueObjectsToValueObjectDirectoryRector;
use Rector\Tests\Autodiscovery\Rector\Class_\MoveValueObjectsToValueObjectDirectoryRector\Source\ObviousValueObjectInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MoveValueObjectsToValueObjectDirectoryRector::class)
        ->configure([
            MoveValueObjectsToValueObjectDirectoryRector::TYPES => [ObviousValueObjectInterface::class],
            MoveValueObjectsToValueObjectDirectoryRector::SUFFIXES => ['Search'],
            MoveValueObjectsToValueObjectDirectoryRector::ENABLE_VALUE_OBJECT_GUESSING => true,
        ]);
};
