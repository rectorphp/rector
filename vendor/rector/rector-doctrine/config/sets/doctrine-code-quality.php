<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Config\RectorConfig;
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
use Rector\Doctrine\Rector\Property\TypedPropertyFromColumnTypeRector;
use Rector\Doctrine\Rector\Property\TypedPropertyFromToManyRelationTypeRector;
use Rector\Doctrine\Rector\Property\TypedPropertyFromToOneRelationTypeRector;
use Rector\Privatization\Rector\MethodCall\ReplaceStringWithClassConstantRector;
use Rector\Privatization\ValueObject\ReplaceStringWithClassConstant;
use Rector\Transform\Rector\Attribute\AttributeKeyToClassConstFetchRector;
use Rector\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector;
use Rector\Transform\ValueObject\AttributeKeyToClassConstFetch;
use Rector\Transform\ValueObject\ServiceGetterToConstructorInjection;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $services = $rectorConfig->services();
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
    // typed properties in entities from annotations/attributes
    $services->set(\Rector\Doctrine\Rector\Property\TypedPropertyFromColumnTypeRector::class);
    $services->set(\Rector\Doctrine\Rector\Property\TypedPropertyFromToOneRelationTypeRector::class);
    $services->set(\Rector\Doctrine\Rector\Property\TypedPropertyFromToManyRelationTypeRector::class);
    $services->set(\Rector\Transform\Rector\Attribute\AttributeKeyToClassConstFetchRector::class)->configure([new \Rector\Transform\ValueObject\AttributeKeyToClassConstFetch('Doctrine\\ORM\\Mapping\\Column', 'type', 'Doctrine\\DBAL\\Types\\Types', ['array' => 'ARRAY', 'ascii_string' => 'ASCII_STRING', 'bigint' => 'BIGINT', 'binary' => 'BINARY', 'blob' => 'BLOB', 'boolean' => 'BOOLEAN', 'date' => 'DATE_MUTABLE', 'date_immutable' => 'DATE_IMMUTABLE', 'dateinterval' => 'DATEINTERVAL', 'datetime' => 'DATETIME_MUTABLE', 'datetime_immutable' => 'DATETIME_IMMUTABLE', 'datetimetz' => 'DATETIMETZ_MUTABLE', 'datetimetz_immutable' => 'DATETIMETZ_IMMUTABLE', 'decimal' => 'DECIMAL', 'float' => 'FLOAT', 'guid' => 'GUID', 'integer' => 'INTEGER', 'json' => 'JSON', 'object' => 'OBJECT', 'simple_array' => 'SIMPLE_ARRAY', 'smallint' => 'SMALLINT', 'string' => 'STRING', 'text' => 'TEXT', 'time' => 'TIME_MUTABLE', 'time_immutable' => 'TIME_IMMUTABLE'])]);
    $services->set(\Rector\Privatization\Rector\MethodCall\ReplaceStringWithClassConstantRector::class)->configure([new \Rector\Privatization\ValueObject\ReplaceStringWithClassConstant('Doctrine\\ORM\\QueryBuilder', 'orderBy', 1, 'Doctrine\\Common\\Collections\\Criteria', \true), new \Rector\Privatization\ValueObject\ReplaceStringWithClassConstant('Doctrine\\ORM\\QueryBuilder', 'addOrderBy', 1, 'Doctrine\\Common\\Collections\\Criteria', \true)]);
    $services->set(\Rector\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector::class)->configure([new \Rector\Transform\ValueObject\ServiceGetterToConstructorInjection('Doctrine\\Common\\Persistence\\ManagerRegistry', 'getConnection', 'Doctrine\\DBAL\\Connection'), new \Rector\Transform\ValueObject\ServiceGetterToConstructorInjection('Doctrine\\ORM\\EntityManagerInterface', 'getConfiguration', 'Doctrine\\ORM\\Configuration')]);
};
