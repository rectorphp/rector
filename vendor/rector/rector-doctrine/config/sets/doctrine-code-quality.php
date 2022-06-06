<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Doctrine\Rector\Class_\InitializeDefaultEntityCollectionRector;
use RectorPrefix20220606\Rector\Doctrine\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector;
use RectorPrefix20220606\Rector\Doctrine\Rector\Class_\MoveCurrentDateTimeDefaultInEntityToConstructorRector;
use RectorPrefix20220606\Rector\Doctrine\Rector\Class_\RemoveRedundantDefaultClassAnnotationValuesRector;
use RectorPrefix20220606\Rector\Doctrine\Rector\ClassMethod\MakeEntitySetterNullabilityInSyncWithPropertyRector;
use RectorPrefix20220606\Rector\Doctrine\Rector\Property\ChangeBigIntEntityPropertyToIntTypeRector;
use RectorPrefix20220606\Rector\Doctrine\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector;
use RectorPrefix20220606\Rector\Doctrine\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector;
use RectorPrefix20220606\Rector\Doctrine\Rector\Property\MakeEntityDateTimePropertyDateTimeInterfaceRector;
use RectorPrefix20220606\Rector\Doctrine\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector;
use RectorPrefix20220606\Rector\Doctrine\Rector\Property\TypedPropertyFromColumnTypeRector;
use RectorPrefix20220606\Rector\Doctrine\Rector\Property\TypedPropertyFromToManyRelationTypeRector;
use RectorPrefix20220606\Rector\Doctrine\Rector\Property\TypedPropertyFromToOneRelationTypeRector;
use RectorPrefix20220606\Rector\Privatization\Rector\MethodCall\ReplaceStringWithClassConstantRector;
use RectorPrefix20220606\Rector\Privatization\ValueObject\ReplaceStringWithClassConstant;
use RectorPrefix20220606\Rector\Transform\Rector\Attribute\AttributeKeyToClassConstFetchRector;
use RectorPrefix20220606\Rector\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector;
use RectorPrefix20220606\Rector\Transform\ValueObject\AttributeKeyToClassConstFetch;
use RectorPrefix20220606\Rector\Transform\ValueObject\ServiceGetterToConstructorInjection;
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
    // typed properties in entities from annotations/attributes
    $rectorConfig->rule(TypedPropertyFromColumnTypeRector::class);
    $rectorConfig->rule(TypedPropertyFromToOneRelationTypeRector::class);
    $rectorConfig->rule(TypedPropertyFromToManyRelationTypeRector::class);
    $rectorConfig->ruleWithConfiguration(AttributeKeyToClassConstFetchRector::class, [new AttributeKeyToClassConstFetch('Doctrine\\ORM\\Mapping\\Column', 'type', 'Doctrine\\DBAL\\Types\\Types', ['array' => 'ARRAY', 'ascii_string' => 'ASCII_STRING', 'bigint' => 'BIGINT', 'binary' => 'BINARY', 'blob' => 'BLOB', 'boolean' => 'BOOLEAN', 'date' => 'DATE_MUTABLE', 'date_immutable' => 'DATE_IMMUTABLE', 'dateinterval' => 'DATEINTERVAL', 'datetime' => 'DATETIME_MUTABLE', 'datetime_immutable' => 'DATETIME_IMMUTABLE', 'datetimetz' => 'DATETIMETZ_MUTABLE', 'datetimetz_immutable' => 'DATETIMETZ_IMMUTABLE', 'decimal' => 'DECIMAL', 'float' => 'FLOAT', 'guid' => 'GUID', 'integer' => 'INTEGER', 'json' => 'JSON', 'object' => 'OBJECT', 'simple_array' => 'SIMPLE_ARRAY', 'smallint' => 'SMALLINT', 'string' => 'STRING', 'text' => 'TEXT', 'time' => 'TIME_MUTABLE', 'time_immutable' => 'TIME_IMMUTABLE'])]);
    $rectorConfig->ruleWithConfiguration(ReplaceStringWithClassConstantRector::class, [new ReplaceStringWithClassConstant('Doctrine\\ORM\\QueryBuilder', 'orderBy', 1, 'Doctrine\\Common\\Collections\\Criteria', \true), new ReplaceStringWithClassConstant('Doctrine\\ORM\\QueryBuilder', 'addOrderBy', 1, 'Doctrine\\Common\\Collections\\Criteria', \true)]);
    $rectorConfig->ruleWithConfiguration(ServiceGetterToConstructorInjectionRector::class, [new ServiceGetterToConstructorInjection('Doctrine\\Common\\Persistence\\ManagerRegistry', 'getConnection', 'Doctrine\\DBAL\\Connection'), new ServiceGetterToConstructorInjection('Doctrine\\ORM\\EntityManagerInterface', 'getConfiguration', 'Doctrine\\ORM\\Configuration')]);
};
