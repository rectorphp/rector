<?php

declare(strict_types=1);

use Rector\Autodiscovery\Rector\FileNode\MoveEntitiesToEntityDirectoryRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MoveEntitiesToEntityDirectoryRector::class);
};
