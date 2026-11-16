<?php

declare (strict_types=1);
namespace RectorPrefix202611;

use Rector\Config\RectorConfig;
use Rector\Doctrine\CodeQuality\Rector\Property\ColumnTypeToTypesConstantRector;
use Rector\Doctrine\CodeQuality\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector;
use Rector\Doctrine\CodeQuality\Rector\Property\TypedPropertyFromColumnTypeRector;
use Rector\Doctrine\CodeQuality\Rector\Property\TypedPropertyFromToOneRelationTypeRector;
use Rector\Doctrine\TypedCollections\Rector\Class_\CompleteReturnDocblockFromToManyRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([
        CorrectDefaultTypesOnEntityPropertyRector::class,
        // typed properties in entities from annotations/attributes
        TypedPropertyFromColumnTypeRector::class,
        TypedPropertyFromToOneRelationTypeRector::class,
        CompleteReturnDocblockFromToManyRector::class,
        ColumnTypeToTypesConstantRector::class,
    ]);
};
