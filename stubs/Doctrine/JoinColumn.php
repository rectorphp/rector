<?php

namespace Doctrine\ORM\Mapping;

if (interface_exists('Doctrine\ORM\Mapping\JoinColumn')) {
    return;
}

/**
 * @Annotation
 * @Target({"PROPERTY","ANNOTATION"})
 */
final class JoinColumn implements Annotation
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $referencedColumnName = 'id';

    /**
     * @var boolean
     */
    public $unique = false;

    /**
     * @var boolean
     */
    public $nullable = true;

    /**
     * @var mixed
     */
    public $onDelete;

    /**
     * @var string
     */
    public $columnDefinition;

    /**
     * Field name used in non-object hydration (array/scalar).
     *
     * @var string
     */
    public $fieldName;
}
