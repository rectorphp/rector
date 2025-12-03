<?php

declare (strict_types=1);
namespace Rector\Doctrine\Enum;

final class MappingClass
{
    public const COLUMN = 'Doctrine\ORM\Mapping\Column';
    public const TABLE = 'Doctrine\ORM\Mapping\Table';
    public const ENTITY = 'Doctrine\ORM\Mapping\Entity';
    public const EMBEDDABLE = 'Doctrine\ORM\Mapping\Embeddable';
    public const DISCRIMINATOR_MAP = 'Doctrine\ORM\Mapping\DiscriminatorMap';
    public const INHERITANCE_TYPE = 'Doctrine\ORM\Mapping\InheritanceType';
    public const DISCRIMINATOR_COLUMN = 'Doctrine\ORM\Mapping\DiscriminatorColumn';
    public const EMBEDDED = 'Doctrine\ORM\Mapping\Embedded';
    public const GEDMO_SOFT_DELETEABLE = 'Gedmo\Mapping\Annotation\SoftDeleteable';
    public const GEDMO_TIMESTAMPABLE = 'Gedmo\Mapping\Annotation\Timestampable';
    public const ID = 'Doctrine\ORM\Mapping\Id';
    public const GENERATED_VALUE = 'Doctrine\ORM\Mapping\GeneratedValue';
    public const INDEX = 'Doctrine\ORM\Mapping\Index';
    public const INVERSE_JOIN_COLUMN = 'Doctrine\ORM\Mapping\InverseJoinColumn';
    public const JOIN_TABLE = 'Doctrine\ORM\Mapping\JoinTable';
    public const MANY_TO_MANY = 'Doctrine\ORM\Mapping\ManyToMany';
    public const MANY_TO_ONE = 'Doctrine\ORM\Mapping\ManyToOne';
    public const JOIN_COLUMN = 'Doctrine\ORM\Mapping\JoinColumn';
    public const ORDER_BY = 'Doctrine\ORM\Mapping\OrderBy';
    public const ONE_TO_MANY = 'Doctrine\ORM\Mapping\OneToMany';
    public const ONE_TO_ONE = 'Doctrine\ORM\Mapping\OneToOne';
    public const UNIQUE_CONSTRAINT = 'Doctrine\ORM\Mapping\UniqueConstraint';
}
