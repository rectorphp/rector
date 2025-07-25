<?php

declare (strict_types=1);
namespace RectorPrefix202507;

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\Rector\Property\NestedAnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationPropertyToAttributeClass;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Php80\ValueObject\NestedAnnotationToAttribute;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(NestedAnnotationToAttributeRector::class, [new NestedAnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\Indexes', [new AnnotationPropertyToAttributeClass('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\Index'), new AnnotationPropertyToAttributeClass('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\UniqueIndex')], \true)]);
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\File\\ChunkSize'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\File\\Filename'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\File\\Length'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\File\\Metadata'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\File\\UploadDate'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\AlsoLoad'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\ChangeTrackingPolicy'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\DefaultDiscriminatorValue'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\DiscriminatorField'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\DiscriminatorMap'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\DiscriminatorValue'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\Document'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\EmbeddedDocument'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\EmbedMany'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\EmbedOne'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\Field'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\File'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\HasLifecycleCallbacks'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\Id'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\Index'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\InheritanceType'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\Lock'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\MappedSuperclass'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\PostLoad'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\PostPersist'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\PostRemove'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\PostUpdate'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\PreFlush'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\PreLoad'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\PrePersist'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\PreRemove'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\PreUpdate'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\QueryResultDocument'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\ReadPreference'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\ReferenceMany'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\ReferenceOne'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\ShardKey'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\UniqueIndex'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\Validation'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\Version'),
        new AnnotationToAttribute('Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\View'),
        // bundle
        new AnnotationToAttribute('Doctrine\\Bundle\\MongoDBBundle\\Validator\\Constraints\\Unique'),
    ]);
};
