<?php

namespace Doctrine\ORM\Mapping;

if (interface_exists('Doctrine\ORM\Mapping\ManyToOne')) {
    return;
}

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class ManyToOne implements Annotation
{
    /**
     * @var string
     */
    public $targetEntity;

    /**
     * @var array<string>
     */
    public $cascade;

    /**
     * The fetching strategy to use for the association.
     *
     * @var string
     *
     * @Enum({"LAZY", "EAGER", "EXTRA_LAZY"})
     */
    public $fetch = 'LAZY';

    /**
     * @var string
     */
    public $inversedBy;
}
