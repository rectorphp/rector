<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Doctrine\CodeQuality\AttributeTransformer\ClassAttributeTransformer\EmbeddableClassAttributeTransformer;
use Rector\Doctrine\CodeQuality\AttributeTransformer\ClassAttributeTransformer\EntityClassAttributeTransformer;
use Rector\Doctrine\CodeQuality\AttributeTransformer\ClassAttributeTransformer\InheritanceClassAttributeTransformer;
use Rector\Doctrine\CodeQuality\AttributeTransformer\ClassAttributeTransformer\SoftDeletableClassAttributeTransformer;
use Rector\Doctrine\CodeQuality\AttributeTransformer\ClassAttributeTransformer\TableClassAttributeTransformer;
use Rector\Doctrine\CodeQuality\AttributeTransformer\PropertyAttributeTransformer\ColumnAttributeTransformer;
use Rector\Doctrine\CodeQuality\AttributeTransformer\PropertyAttributeTransformer\EmbeddedPropertyAttributeTransformer;
use Rector\Doctrine\CodeQuality\AttributeTransformer\PropertyAttributeTransformer\GedmoTimestampableAttributeTransformer;
use Rector\Doctrine\CodeQuality\AttributeTransformer\PropertyAttributeTransformer\IdAttributeTransformer;
use Rector\Doctrine\CodeQuality\AttributeTransformer\PropertyAttributeTransformer\IdColumnAttributeTransformer;
use Rector\Doctrine\CodeQuality\AttributeTransformer\PropertyAttributeTransformer\IdGeneratorAttributeTransformer;
use Rector\Doctrine\CodeQuality\AttributeTransformer\PropertyAttributeTransformer\InverseJoinColumnAttributeTransformer;
use Rector\Doctrine\CodeQuality\AttributeTransformer\PropertyAttributeTransformer\JoinColumnAttributeTransformer;
use Rector\Doctrine\CodeQuality\AttributeTransformer\PropertyAttributeTransformer\JoinTableAttributeTransformer;
use Rector\Doctrine\CodeQuality\AttributeTransformer\PropertyAttributeTransformer\ManyToManyAttributeTransformer;
use Rector\Doctrine\CodeQuality\AttributeTransformer\PropertyAttributeTransformer\ManyToOneAttributeTransformer;
use Rector\Doctrine\CodeQuality\AttributeTransformer\PropertyAttributeTransformer\OneToManyAttributeTransformer;
use Rector\Doctrine\CodeQuality\AttributeTransformer\PropertyAttributeTransformer\OrderByAttributeTransformer;
use Rector\Doctrine\CodeQuality\AttributeTransformer\YamlToAttributeTransformer;
use Rector\Doctrine\CodeQuality\Contract\ClassAttributeTransformerInterface;
use Rector\Doctrine\CodeQuality\Contract\PropertyAttributeTransformerInterface;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->autotagInterface(ClassAttributeTransformerInterface::class);
    $rectorConfig->autotagInterface(PropertyAttributeTransformerInterface::class);
    // for yaml to class attribute transformer
    $rectorConfig->singleton(EntityClassAttributeTransformer::class);
    $rectorConfig->singleton(SoftDeletableClassAttributeTransformer::class);
    $rectorConfig->singleton(TableClassAttributeTransformer::class);
    $rectorConfig->singleton(EmbeddableClassAttributeTransformer::class);
    $rectorConfig->singleton(InheritanceClassAttributeTransformer::class);
    // for yaml to property attribute transformer
    $rectorConfig->singleton(ColumnAttributeTransformer::class);
    $rectorConfig->singleton(EmbeddedPropertyAttributeTransformer::class);
    $rectorConfig->singleton(GedmoTimestampableAttributeTransformer::class);
    $rectorConfig->singleton(IdAttributeTransformer::class);
    $rectorConfig->singleton(IdColumnAttributeTransformer::class);
    $rectorConfig->singleton(IdGeneratorAttributeTransformer::class);
    $rectorConfig->singleton(ManyToManyAttributeTransformer::class);
    $rectorConfig->singleton(ManyToOneAttributeTransformer::class);
    $rectorConfig->singleton(OneToManyAttributeTransformer::class);
    $rectorConfig->singleton(JoinTableAttributeTransformer::class);
    $rectorConfig->singleton(JoinColumnAttributeTransformer::class);
    $rectorConfig->singleton(InverseJoinColumnAttributeTransformer::class);
    $rectorConfig->singleton(OrderByAttributeTransformer::class);
    $rectorConfig->when(YamlToAttributeTransformer::class)->needs('$classAttributeTransformers')->giveTagged(ClassAttributeTransformerInterface::class);
    $rectorConfig->when(YamlToAttributeTransformer::class)->needs('$propertyAttributeTransformers')->giveTagged(PropertyAttributeTransformerInterface::class);
};
