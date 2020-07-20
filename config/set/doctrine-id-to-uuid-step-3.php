<?php

declare(strict_types=1);

use Rector\Doctrine\Rector\Class_\AddUuidMirrorForRelationPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    # add relations uuid properties
    $services->set(AddUuidMirrorForRelationPropertyRector::class);
};
