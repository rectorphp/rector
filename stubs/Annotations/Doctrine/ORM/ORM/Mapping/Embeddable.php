<?php

namespace Doctrine\ORM\Mapping;

// mimics @see https://github.com/doctrine/orm/blob/master/lib/Doctrine/ORM/Annotation/Embeddable.php

if (class_exists('Doctrine\ORM\Mapping\Embeddable')) {
    return;
}

/**
 * @Annotation
 * @Target("CLASS")
 */
class Embeddable implements Annotation
{
}
