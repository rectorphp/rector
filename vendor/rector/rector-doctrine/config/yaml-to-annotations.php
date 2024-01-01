<?php

declare (strict_types=1);
namespace RectorPrefix202401;

use Rector\Config\RectorConfig;
use Rector\Doctrine\CodeQuality\AnnotationTransformer\ClassAnnotationTransformer\EmbeddableClassAnnotationTransformer;
use Rector\Doctrine\CodeQuality\AnnotationTransformer\ClassAnnotationTransformer\EntityClassAnnotationTransformer;
use Rector\Doctrine\CodeQuality\AnnotationTransformer\ClassAnnotationTransformer\InheritanceClassAnnotationTransformer;
use Rector\Doctrine\CodeQuality\AnnotationTransformer\ClassAnnotationTransformer\SoftDeletableClassAnnotationTransformer;
use Rector\Doctrine\CodeQuality\AnnotationTransformer\ClassAnnotationTransformer\TableClassAnnotationTransformer;
use Rector\Doctrine\CodeQuality\AnnotationTransformer\PropertyAnnotationTransformer\ColumnAnnotationTransformer;
use Rector\Doctrine\CodeQuality\AnnotationTransformer\PropertyAnnotationTransformer\EmbeddedPropertyAnnotationTransformer;
use Rector\Doctrine\CodeQuality\AnnotationTransformer\PropertyAnnotationTransformer\GedmoTimestampableAnnotationTransformer;
use Rector\Doctrine\CodeQuality\AnnotationTransformer\PropertyAnnotationTransformer\IdAnnotationTransformer;
use Rector\Doctrine\CodeQuality\AnnotationTransformer\PropertyAnnotationTransformer\IdColumnAnnotationTransformer;
use Rector\Doctrine\CodeQuality\AnnotationTransformer\PropertyAnnotationTransformer\IdGeneratorAnnotationTransformer;
use Rector\Doctrine\CodeQuality\AnnotationTransformer\PropertyAnnotationTransformer\ManyToOneAnnotationTransformer;
use Rector\Doctrine\CodeQuality\AnnotationTransformer\PropertyAnnotationTransformer\OneToManyAnnotationTransformer;
use Rector\Doctrine\CodeQuality\AnnotationTransformer\YamlToAnnotationTransformer;
use Rector\Doctrine\CodeQuality\Contract\ClassAnnotationTransformerInterface;
use Rector\Doctrine\CodeQuality\Contract\PropertyAnnotationTransformerInterface;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->autotagInterface(ClassAnnotationTransformerInterface::class);
    $rectorConfig->autotagInterface(PropertyAnnotationTransformerInterface::class);
    // for yaml to annotation transformer
    $rectorConfig->singleton(EntityClassAnnotationTransformer::class);
    $rectorConfig->singleton(SoftDeletableClassAnnotationTransformer::class);
    $rectorConfig->singleton(TableClassAnnotationTransformer::class);
    $rectorConfig->singleton(EmbeddableClassAnnotationTransformer::class);
    $rectorConfig->singleton(InheritanceClassAnnotationTransformer::class);
    $rectorConfig->singleton(ColumnAnnotationTransformer::class);
    $rectorConfig->singleton(EmbeddedPropertyAnnotationTransformer::class);
    $rectorConfig->singleton(GedmoTimestampableAnnotationTransformer::class);
    $rectorConfig->singleton(IdAnnotationTransformer::class);
    $rectorConfig->singleton(IdColumnAnnotationTransformer::class);
    $rectorConfig->singleton(IdGeneratorAnnotationTransformer::class);
    $rectorConfig->singleton(ManyToOneAnnotationTransformer::class);
    $rectorConfig->singleton(OneToManyAnnotationTransformer::class);
    $rectorConfig->when(YamlToAnnotationTransformer::class)->needs('$classAnnotationTransformers')->giveTagged(ClassAnnotationTransformerInterface::class);
    $rectorConfig->when(YamlToAnnotationTransformer::class)->needs('$propertyAnnotationTransformers')->giveTagged(PropertyAnnotationTransformerInterface::class);
};
