<?php

namespace Doctrine\ORM\Mapping;

// mimics @see https://github.com/doctrine/orm/blob/master/lib/Doctrine/ORM/Annotation/Embedded.php

if (class_exists('Doctrine\ORM\Mapping\Embedded')) {
    return;
}

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class Embedded implements Annotation
{
    /**
     * @Required
     * @var string
     */
    public $class;

    /**
     * @var mixed
     */
    public $columnPrefix;
}
