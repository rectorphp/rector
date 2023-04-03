<?php

declare (strict_types=1);
namespace RectorPrefix202304;

use Rector\Config\RectorConfig;
use Rector\Doctrine\Rector\Class_\InitializeDefaultEntityCollectionRector;
use Rector\Doctrine\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector;
use Rector\Doctrine\Rector\Class_\MoveCurrentDateTimeDefaultInEntityToConstructorRector;
use Rector\Doctrine\Rector\Class_\RemoveEmptyTableAttributeRector;
use Rector\Doctrine\Rector\Class_\RemoveRedundantDefaultClassAnnotationValuesRector;
use Rector\Doctrine\Rector\ClassMethod\MakeEntitySetterNullabilityInSyncWithPropertyRector;
use Rector\Doctrine\Rector\Property\ChangeBigIntEntityPropertyToIntTypeRector;
use Rector\Doctrine\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector;
use Rector\Doctrine\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector;
use Rector\Doctrine\Rector\Property\MakeEntityDateTimePropertyDateTimeInterfaceRector;
use Rector\Doctrine\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector;
use Rector\Doctrine\Rector\Property\TypedPropertyFromColumnTypeRector;
use Rector\Doctrine\Rector\Property\TypedPropertyFromDoctrineCollectionRector;
use Rector\Doctrine\Rector\Property\TypedPropertyFromToManyRelationTypeRector;
use Rector\Doctrine\Rector\Property\TypedPropertyFromToOneRelationTypeRector;
use Rector\Transform\Rector\Attribute\AttributeKeyToClassConstFetchRector;
use Rector\Transform\ValueObject\AttributeKeyToClassConstFetch;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(ManagerRegistryGetManagerToEntityManagerRector::class);
    $rectorConfig->rule(InitializeDefaultEntityCollectionRector::class);
    $rectorConfig->rule(MakeEntitySetterNullabilityInSyncWithPropertyRector::class);
    $rectorConfig->rule(MakeEntityDateTimePropertyDateTimeInterfaceRector::class);
    $rectorConfig->rule(MoveCurrentDateTimeDefaultInEntityToConstructorRector::class);
    $rectorConfig->rule(CorrectDefaultTypesOnEntityPropertyRector::class);
    $rectorConfig->rule(ChangeBigIntEntityPropertyToIntTypeRector::class);
    $rectorConfig->rule(ImproveDoctrineCollectionDocTypeInEntityRector::class);
    $rectorConfig->rule(RemoveRedundantDefaultPropertyAnnotationValuesRector::class);
    $rectorConfig->rule(RemoveRedundantDefaultClassAnnotationValuesRector::class);
    $rectorConfig->rule(RemoveEmptyTableAttributeRector::class);
    // typed properties in entities from annotations/attributes
    $rectorConfig->rule(TypedPropertyFromColumnTypeRector::class);
    $rectorConfig->rule(TypedPropertyFromToOneRelationTypeRector::class);
    $rectorConfig->rule(TypedPropertyFromToManyRelationTypeRector::class);
    $rectorConfig->rule(TypedPropertyFromDoctrineCollectionRector::class);
    $rectorConfig->ruleWithConfiguration(AttributeKeyToClassConstFetchRector::class, [new AttributeKeyToClassConstFetch('Doctrine\\ORM\\Mapping\\Column', 'type', 'Doctrine\\DBAL\\Types\\Types', ['array' => 'ARRAY', 'ascii_string' => 'ASCII_STRING', 'bigint' => 'BIGINT', 'binary' => 'BINARY', 'blob' => 'BLOB', 'boolean' => 'BOOLEAN', 'date' => 'DATE_MUTABLE', 'date_immutable' => 'DATE_IMMUTABLE', 'dateinterval' => 'DATEINTERVAL', 'datetime' => 'DATETIME_MUTABLE', 'datetime_immutable' => 'DATETIME_IMMUTABLE', 'datetimetz' => 'DATETIMETZ_MUTABLE', 'datetimetz_immutable' => 'DATETIMETZ_IMMUTABLE', 'decimal' => 'DECIMAL', 'float' => 'FLOAT', 'guid' => 'GUID', 'integer' => 'INTEGER', 'json' => 'JSON', 'object' => 'OBJECT', 'simple_array' => 'SIMPLE_ARRAY', 'smallint' => 'SMALLINT', 'string' => 'STRING', 'text' => 'TEXT', 'time' => 'TIME_MUTABLE', 'time_immutable' => 'TIME_IMMUTABLE'])]);
};
