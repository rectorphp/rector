<?php

namespace Doctrine\ORM\Mapping;

if (interface_exists('Doctrine\ORM\Mapping\Id')) {
    return;
}

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class Id implements Annotation
{
}
