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
     * Internal php-parser name.
     * Do not change this even if you want!
     *
     * @var string
     */
    public const ORIGINAL_NODE = 'origNode';

    /**
     * Internal php-parser name. @see \PhpParser\NodeVisitor\NameResolver
     * Do not change this even if you want!
     *
     * @var string
     */
    public const RESOLVED_NAME = 'resolvedName';

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

    /**
     * @var string
     */
    public const COMMENTS = 'comments';

    /**
     * PHPStan-based type scope.
     *
     * @var string
     */
    public const SCOPE = 'scope';
}
