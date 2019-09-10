<?php

namespace Doctrine\ORM\Mapping;

if (class_exists('Doctrine\ORM\Mapping\Id')) {
    return;
}

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Id implements Annotation
{
}
