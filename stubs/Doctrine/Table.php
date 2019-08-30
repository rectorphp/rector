<?php

namespace Doctrine\ORM\Mapping;

if (interface_exists('Doctrine\ORM\Mapping\Table')) {
    return;
}

/**
 * @Annotation
 * @Target("CLASS")
 */
final class Table implements Annotation
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $schema;

    /**
     * @var array<\Doctrine\ORM\Mapping\Index>
     */
    public $indexes;

    /**
     * @var array<\Doctrine\ORM\Mapping\UniqueConstraint>
     */
    public $uniqueConstraints;

    /**
     * @var array
     */
    public $options = [];
}
