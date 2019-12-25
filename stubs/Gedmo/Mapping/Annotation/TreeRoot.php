<?php declare(strict_types=1);

namespace Gedmo\Mapping\Annotation;

if (class_exists('Gedmo\Mapping\Annotation\TreeRoot')) {
    return;
}

/**
 * @Annotation
 */
class TreeRoot
{
    /** @var string $identifierMethod */
    public $identifierMethod;
}
