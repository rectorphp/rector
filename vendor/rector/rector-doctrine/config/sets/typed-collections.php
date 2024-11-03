<?php

declare (strict_types=1);
namespace RectorPrefix202411;

use Rector\Config\RectorConfig;
use Rector\Doctrine\CodeQuality\Rector\Class_\AddReturnDocBlockToCollectionPropertyGetterByToManyAnnotationRector;
use Rector\Doctrine\CodeQuality\Rector\Class_\ExplicitRelationCollectionRector;
use Rector\Doctrine\CodeQuality\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector;
use Rector\Doctrine\CodeQuality\Rector\Property\TypedPropertyFromToManyRelationTypeRector;
return RectorConfig::configure()->withRules([ExplicitRelationCollectionRector::class, AddReturnDocBlockToCollectionPropertyGetterByToManyAnnotationRector::class, TypedPropertyFromToManyRelationTypeRector::class, ImproveDoctrineCollectionDocTypeInEntityRector::class]);
