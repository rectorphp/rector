<?php declare(strict_types=1);

namespace Doctrine\ORM\Mapping;

if (class_exists('Doctrine\ORM\Mapping\UniqueConstraint')) {
    return;
}

/**
 * @Annotation
 * @Target("ANNOTATION")
 */
final class UniqueConstraint implements Annotation
{
    /** @var string */
    public $name;

    /** @var string[] */
    public $columns;

    /** @var string[] */
    public $flags = [];

    /** @var array */
    public $options = [];
}
