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
     * Class, interface or trait FQN types.
     *
     * @var string
     */
    public const TYPES = 'types';

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
    public const CLASS_NAME = 'className';

    /**
     * @var string
     */
    public const METHOD_NAME = 'methodName';

    /**
     * @var string
     */
    public const PARENT_CLASS_NAME = 'parentClassName';

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

    /**
     * @var string
     */
    public const USE_STATEMENTS = 'useStatements';

    /**
     * @var string
     */
    public const NAMESPACE_NAME = 'namespace';

    /**
     * @var string
     */
    public const METHOD_CALL = 'methodCall';

    /**
     * @var string
     */
    public const METHOD_NODE = 'methodNode';

    /**
     * @var string
     */
    public const NAMESPACE_NODE = 'namespaceNode';

    /**
     * @var string
     */
    public const USE_NODES = 'useNodes';

    /**
     * @var string
     */
    public const RETURN_TYPES = 'returnTypes';

    /**
     * @var string
     */
    public const COMMENTS = 'comments';

    /**
     * Internal php-parser name.
     * Do not change this even if you want!
     *
     * @var string
     */
    public const NAMESPACED_NAME = 'namespacedName';
}
