<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use Rector\Doctrine\Rector\Class_\InitializeDefaultEntityCollectionRector;
use Rector\Doctrine\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector;
use Rector\Doctrine\Rector\Class_\MoveCurrentDateTimeDefaultInEntityToConstructorRector;
use Rector\Doctrine\Rector\Class_\RemoveRedundantDefaultClassAnnotationValuesRector;
use Rector\Doctrine\Rector\ClassMethod\MakeEntitySetterNullabilityInSyncWithPropertyRector;
use Rector\Doctrine\Rector\Property\ChangeBigIntEntityPropertyToIntTypeRector;
use Rector\Doctrine\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector;
use Rector\Doctrine\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector;
use Rector\Doctrine\Rector\Property\MakeEntityDateTimePropertyDateTimeInterfaceRector;
use Rector\Doctrine\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector;
use Rector\Privatization\Rector\MethodCall\ReplaceStringWithClassConstantRector;
use Rector\Privatization\ValueObject\ReplaceStringWithClassConstant;
use Rector\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector;
use Rector\Transform\ValueObject\ServiceGetterToConstructorInjection;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
return static function (ContainerConfigurator $containerConfigurator) : void {
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
    $services->set(ReplaceStringWithClassConstantRector::class)->call('configure', [[ReplaceStringWithClassConstantRector::REPLACE_STRING_WITH_CLASS_CONSTANT => ValueObjectInliner::inline([new ReplaceStringWithClassConstant('Doctrine\\ORM\\QueryBuilder', 'orderBy', 1, 'Doctrine\\Common\\Collections\\Criteria')])]]);
    $services->set(ServiceGetterToConstructorInjectionRector::class)->call('configure', [[ServiceGetterToConstructorInjectionRector::METHOD_CALL_TO_SERVICES => ValueObjectInliner::inline([new ServiceGetterToConstructorInjection('Doctrine\\Common\\Persistence\\ManagerRegistry', 'getConnection', 'Doctrine\\DBAL\\Connection'), new ServiceGetterToConstructorInjection('Doctrine\\ORM\\EntityManagerInterface', 'getConfiguration', 'Doctrine\\ORM\\Configuration')])]]);
};
