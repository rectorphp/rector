<?php declare(strict_types=1);

namespace Doctrine\ORM\Mapping;

if (class_exists('Doctrine\ORM\Mapping\CustomIdGenerator')) {
    return;
}

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class CustomIdGenerator implements Annotation
{
    /**
     * @var string
     */
    public $class;

    /**
     * @var string[]
     */
    public $arguments = [];
}
