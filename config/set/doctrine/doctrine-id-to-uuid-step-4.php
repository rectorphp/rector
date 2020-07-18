<?php

declare(strict_types=1);

use Rector\Doctrine\Rector\ClassMethod\ChangeGetIdTypeToUuidRector;
use Rector\Doctrine\Rector\ClassMethod\ChangeSetIdTypeToUuidRector;
use Rector\Doctrine\Rector\Property\AddUuidAnnotationsToIdPropertyRector;
use Rector\Doctrine\Rector\Property\RemoveTemporaryUuidColumnPropertyRector;
use Rector\Doctrine\Rector\Property\RemoveTemporaryUuidRelationPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    # properties
    $services->set(AddUuidAnnotationsToIdPropertyRector::class);

    $services->set(RemoveTemporaryUuidColumnPropertyRector::class);

    $services->set(RemoveTemporaryUuidRelationPropertyRector::class);

    # methods
    $services->set(ChangeGetIdTypeToUuidRector::class);

    $services->set(ChangeSetIdTypeToUuidRector::class);
};
