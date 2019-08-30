<?php

namespace Doctrine\ORM\Mapping;

if (interface_exists('Doctrine\ORM\Mapping\JoinTable')) {
    return;
}

/**
 * @Annotation
 * @Target({"PROPERTY","ANNOTATION"})
 */
final class JoinTable implements Annotation
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
     * @var array<\Doctrine\ORM\Mapping\JoinColumn>
     */
    public $joinColumns = [];

    /**
     * @var array<\Doctrine\ORM\Mapping\JoinColumn>
     */
    public $inverseJoinColumns = [];
}
