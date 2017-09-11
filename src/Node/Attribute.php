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
     * Class, interface or trait FQN type.
     *
     * @var string
     */
    public const TYPE = 'type';

    /**
     * In class, in interface, in trait, in method or in function
     *
     * @var string
     */
    public const SCOPE = 'scope';

    /**
     * @var string
     */
    public const SCOPE_NODE = 'scopeNode';

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
    public const CLASS_NODE = 'classNode';

    /**
     * @var string
     */
    public const PARENT_NODE = 'parentNode';

    /**
     * @var string
     */
    public const PREVIOUS_NODE = 'prevNode';

    /**
     * @var string
     */
    public const NEXT_NODE = 'nextNode';

    private function __construct()
    {
    }
}
