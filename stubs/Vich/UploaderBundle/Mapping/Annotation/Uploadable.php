<?php

declare(strict_types=1);

namespace Vich\UploaderBundle\Mapping\Annotation;

if (class_exists('Vich\UploaderBundle\Mapping\Annotation\Uploadable')) {
    return;
}

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Uploadable
{
}
