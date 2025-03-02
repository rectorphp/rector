<?php

declare (strict_types=1);
namespace RectorPrefix202503;

use Rector\Config\RectorConfig;
use Rector\Doctrine\CodeQuality\Rector\Class_\AddReturnDocBlockToCollectionPropertyGetterByToManyAnnotationRector;
use Rector\Doctrine\CodeQuality\Rector\Class_\ExplicitRelationCollectionRector;
use Rector\Doctrine\CodeQuality\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector;
use Rector\Doctrine\CodeQuality\Rector\Property\TypedPropertyFromToManyRelationTypeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([ExplicitRelationCollectionRector::class, AddReturnDocBlockToCollectionPropertyGetterByToManyAnnotationRector::class, TypedPropertyFromToManyRelationTypeRector::class, ImproveDoctrineCollectionDocTypeInEntityRector::class]);
};
