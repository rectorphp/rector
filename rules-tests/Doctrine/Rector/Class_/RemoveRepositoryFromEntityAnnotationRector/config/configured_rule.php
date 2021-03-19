<?php

declare(strict_types=1);

use Rector\Doctrine\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveRepositoryFromEntityAnnotationRector::class);
};
