<?php

declare(strict_types=1);

use Rector\Doctrine\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector;
use Rector\DoctrineCodeQuality\Rector\Class_\InitializeDefaultEntityCollectionRector;
use Rector\DoctrineCodeQuality\Rector\Class_\MoveCurrentDateTimeDefaultInEntityToConstructorRector;
use Rector\DoctrineCodeQuality\Rector\Class_\RemoveRedundantDefaultClassAnnotationValuesRector;
use Rector\DoctrineCodeQuality\Rector\ClassMethod\MakeEntityDateTimePropertyDateTimeInterfaceRector;
use Rector\DoctrineCodeQuality\Rector\ClassMethod\MakeEntitySetterNullabilityInSyncWithPropertyRector;
use Rector\DoctrineCodeQuality\Rector\Property\ChangeBigIntEntityPropertyToIntTypeRector;
use Rector\DoctrineCodeQuality\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector;
use Rector\DoctrineCodeQuality\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector;
use Rector\DoctrineCodeQuality\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ManagerRegistryGetManagerToEntityManagerRector::class);
    $services->set(InitializeDefaultEntityCollectionRector::class);
    $services->set(MakeEntitySetterNullabilityInSyncWithPropertyRector::class);
    $services->set(MakeEntityDateTimePropertyDateTimeInterfaceRector::class);
    $services->set(MoveCurrentDateTimeDefaultInEntityToConstructorRector::class);
    $services->set(CorrectDefaultTypesOnEntityPropertyRector::class);
    $services->set(ChangeBigIntEntityPropertyToIntTypeRector::class);
    $services->set(ImproveDoctrineCollectionDocTypeInEntityRector::class);
    $services->set(RemoveRedundantDefaultPropertyAnnotationValuesRector::class);
    $services->set(RemoveRedundantDefaultClassAnnotationValuesRector::class);
};
