<?php

declare (strict_types=1);
namespace RectorPrefix20210827;

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
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Doctrine\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector::class);
    $services->set(\Rector\Doctrine\Rector\Class_\InitializeDefaultEntityCollectionRector::class);
    $services->set(\Rector\Doctrine\Rector\ClassMethod\MakeEntitySetterNullabilityInSyncWithPropertyRector::class);
    $services->set(\Rector\Doctrine\Rector\Property\MakeEntityDateTimePropertyDateTimeInterfaceRector::class);
    $services->set(\Rector\Doctrine\Rector\Class_\MoveCurrentDateTimeDefaultInEntityToConstructorRector::class);
    $services->set(\Rector\Doctrine\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector::class);
    $services->set(\Rector\Doctrine\Rector\Property\ChangeBigIntEntityPropertyToIntTypeRector::class);
    $services->set(\Rector\Doctrine\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector::class);
    $services->set(\Rector\Doctrine\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector::class);
    $services->set(\Rector\Doctrine\Rector\Class_\RemoveRedundantDefaultClassAnnotationValuesRector::class);
    $services->set(\Rector\Privatization\Rector\MethodCall\ReplaceStringWithClassConstantRector::class)->call('configure', [[\Rector\Privatization\Rector\MethodCall\ReplaceStringWithClassConstantRector::REPLACE_STRING_WITH_CLASS_CONSTANT => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Privatization\ValueObject\ReplaceStringWithClassConstant('Doctrine\\ORM\\QueryBuilder', 'orderBy', 1, 'Doctrine\\Common\\Collections\\Criteria')])]]);
    $services->set(\Rector\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector::class)->call('configure', [[\Rector\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector::METHOD_CALL_TO_SERVICES => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Transform\ValueObject\ServiceGetterToConstructorInjection('Doctrine\\Common\\Persistence\\ManagerRegistry', 'getConnection', 'Doctrine\\DBAL\\Connection'), new \Rector\Transform\ValueObject\ServiceGetterToConstructorInjection('Doctrine\\ORM\\EntityManagerInterface', 'getConfiguration', 'Doctrine\\ORM\\Configuration')])]]);
};
