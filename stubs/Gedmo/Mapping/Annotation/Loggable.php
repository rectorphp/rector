<?php

declare(strict_types=1);

namespace Gedmo\Mapping\Annotation;

if (class_exists('Gedmo\Mapping\Annotation\Loggable')) {
    return;
}

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class Loggable
{
    /**
     * @var string
     */
    public $logEntryClass;
}
