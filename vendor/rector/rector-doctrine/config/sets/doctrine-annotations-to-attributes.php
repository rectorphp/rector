<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [
        // class
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\Table'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\Entity'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\Column'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\UniqueConstraint'),
        // id
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\Id'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\GeneratedValue'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\SequenceGenerator'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\Index'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\CustomIdGenerator'),
        // relations
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\OneToOne'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\OneToMany'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\ManyToMany'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\JoinTable'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\ManyToOne'),
        // join columns
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\JoinColumns'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\JoinColumn'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\InverseJoinColumn'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\OrderBy'),
        // embed
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\Embeddable'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\Embedded'),
        // inheritance
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\MappedSuperclass'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\InheritanceType'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\DiscriminatorColumn'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\DiscriminatorMap'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\Version'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\ChangeTrackingPolicy'),
        // events
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\HasLifecycleCallbacks'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\PostLoad'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\PostPersist'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\PostRemove'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\PostUpdate'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\PreFlush'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\PrePersist'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\PreRemove'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\PreUpdate'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\Cache'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\EntityListeners'),
    ]);
};
