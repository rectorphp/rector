<?php

namespace Doctrine\ORM\Mapping;

if (class_exists('Doctrine\ORM\Mapping\InheritanceType')) {
    return;
}

/**
 * @Annotation
 * @Target("CLASS")
 */
class InheritanceType implements Annotation
{
    /**
     * @Enum({"NONE", "JOINED", "SINGLE_TABLE", "TABLE_PER_CLASS"})
     */
    public $value;
}
