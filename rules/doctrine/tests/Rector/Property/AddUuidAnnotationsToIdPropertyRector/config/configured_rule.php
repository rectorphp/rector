<?php

declare(strict_types=1);

use Rector\Doctrine\Rector\Property\AddUuidAnnotationsToIdPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddUuidAnnotationsToIdPropertyRector::class);
};
