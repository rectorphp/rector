<?php

namespace Doctrine\ORM\Mapping;

if (class_exists('Doctrine\ORM\Mapping\OrderBy')) {
    return;
}

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class OrderBy
{

}
