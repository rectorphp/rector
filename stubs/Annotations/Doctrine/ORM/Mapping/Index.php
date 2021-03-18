<?php

namespace Doctrine\ORM\Mapping;

if (class_exists('Doctrine\ORM\Mapping\Index')) {
    return;
}

/**
 * @Annotation
 * @Target("ANNOTATION")
 */
final class Index implements Annotation
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string[]
     */
    public $columns;

    /**
     * @var string[]
     */
    public $flags;

    /**
     * @var array
     */
    public $options;
}
