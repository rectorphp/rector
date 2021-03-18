<?php

declare(strict_types=1);

namespace Gedmo\Mapping\Annotation;

if (class_exists('Gedmo\Mapping\Annotation\Versioned')) {
    return;
}

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class Versioned
{
}
