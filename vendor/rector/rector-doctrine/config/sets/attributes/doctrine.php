<?php

declare (strict_types=1);
namespace RectorPrefix202410;

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\Rector\Property\NestedAnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationPropertyToAttributeClass;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Php80\ValueObject\NestedAnnotationToAttribute;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(NestedAnnotationToAttributeRector::class, [
        /** @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.13/reference/attributes-reference.html#joincolumn-inversejoincolumn */
        new NestedAnnotationToAttribute('Doctrine\\ORM\\Mapping\\JoinTable', [new AnnotationPropertyToAttributeClass('Doctrine\\ORM\\Mapping\\JoinColumn', 'joinColumns'), new AnnotationPropertyToAttributeClass('Doctrine\\ORM\\Mapping\\InverseJoinColumn', 'inverseJoinColumns', \true)]),
        /** @see https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/annotations-reference.html#joincolumns */
        new NestedAnnotationToAttribute('Doctrine\\ORM\\Mapping\\JoinColumns', [new AnnotationPropertyToAttributeClass('Doctrine\\ORM\\Mapping\\JoinColumn')], \true),
        new NestedAnnotationToAttribute('Doctrine\\ORM\\Mapping\\Table', [new AnnotationPropertyToAttributeClass('Doctrine\\ORM\\Mapping\\Index', 'indexes', \true), new AnnotationPropertyToAttributeClass('Doctrine\\ORM\\Mapping\\UniqueConstraint', 'uniqueConstraints', \true)]),
    ]);
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [
        // class
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\Entity', null, ['repositoryClass']),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\Column'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\UniqueConstraint'),
        // id
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\Id'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\GeneratedValue'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\SequenceGenerator'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\Index'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\CustomIdGenerator', null, ['class']),
        // relations
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\OneToOne', null, ['targetEntity']),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\OneToMany', null, ['targetEntity']),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\ManyToMany', null, ['targetEntity']),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\JoinTable'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\ManyToOne', null, ['targetEntity']),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\OrderBy'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\JoinColumn'),
        // embed
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\Embeddable'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\Embedded', null, ['class']),
        // inheritance
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\MappedSuperclass', null, ['repositoryClass']),
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
        // Overrides
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\AssociationOverrides'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\AssociationOverride'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\AttributeOverrides'),
        new AnnotationToAttribute('Doctrine\\ORM\\Mapping\\AttributeOverride'),
    ]);
};
