<?php

namespace Doctrine\ORM\Mapping;

if (class_exists('Doctrine\ORM\Mapping\GeneratedValue')) {
    return;
}

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class GeneratedValue implements Annotation
{
    /**
     * The type of Id generator.
     *
     * @var string
     * @Enum({"AUTO", "SEQUENCE", "TABLE", "IDENTITY", "NONE", "CUSTOM"})
     */
    public $strategy = 'AUTO';
}
