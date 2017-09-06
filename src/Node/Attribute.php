<?php declare(strict_types=1);

namespace Rector\Node;

/**
 * List of attributes by constants, to prevent any typos.
 *
 * Because typo can causing return "null" instaed of real value - impossible to spot.
 */
final class Attribute
{
    /**
     * @var string
     */
    public const TYPE = 'type';

    /**
     * System name to be found in @see \PhpParser\NodeVisitor\NameResolver
     * Do not change this even if you want!
     *
     * @var string
     */
    public const RESOLVED_NAME = 'resolvedName';

    /**
     * @var string
     */
    public const CLASS_NAME = 'class';

    /**
     * @var string
     */
    public const CLASS_NODE = 'class_node';

    /**
     * @var string
     */
    public const PARENT_NODE = 'parent';

    /**
     * @var string
     */
    public const PREVIOUS_NODE = 'prev';

    /**
     * @var string
     */
    public const NEXT_NODE = 'next';

    private function __construct()
    {
    }
}
