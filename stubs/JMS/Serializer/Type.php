<?php

declare(strict_types=1);

namespace JMS\Serializer\Annotation;

if (class_exists('JMS\Serializer\Annotation\Type')) {
    return;
}

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD","ANNOTATION"})
 */
class Type
{
    /**
     * @Required
     * @var string
     */
    public $name;
}
