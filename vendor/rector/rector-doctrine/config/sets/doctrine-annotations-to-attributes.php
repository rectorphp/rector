<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [
        // class
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\Table'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\Entity'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\Column'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\UniqueConstraint'),
        // id
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\Id'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\GeneratedValue'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\SequenceGenerator'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\Index'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\CustomIdGenerator'),
        // relations
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\OneToOne'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\OneToMany'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\ManyToMany'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\JoinTable'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\ManyToOne'),
        // join columns
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\JoinColumns'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\JoinColumn'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\InverseJoinColumn'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\OrderBy'),
        // embed
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\Embeddable'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\Embedded'),
        // inheritance
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\MappedSuperclass'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\InheritanceType'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\DiscriminatorColumn'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\DiscriminatorMap'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\Version'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\ChangeTrackingPolicy'),
        // events
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\HasLifecycleCallbacks'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\PostLoad'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\PostPersist'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\PostRemove'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\PostUpdate'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\PrePersist'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\PreRemove'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\PreUpdate'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\Cache'),
        new AnnotationToAttribute('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\EntityListeners'),
    ]);
};
