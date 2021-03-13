<?php

declare(strict_types=1);

use Rector\DoctrineCodeQuality\Rector\Class_\InitializeDefaultEntityCollectionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(InitializeDefaultEntityCollectionRector::class);
};
