<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Node;

final class Attribute
{
    /**
     * @var string
     */
    public const SCOPE = 'scope';

    /**
     * @var string
     */
    public const NAMESPACE_NAME = 'namespace';

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
    public const CLASS_NAME = 'className';

    /**
     * @todo split Class node, interface node and trait node, to be compatible with other SpecificNode|null, values
     * @var string
     */
    public const CLASS_NODE = 'classNode';

    /**
     * @var string
     */
    public const PARENT_CLASS_NAME = 'parentClassName';

    /**
     * @var string
     */
    public const METHOD_NAME = 'methodName';

    /**
     * @var string
     */
    public const METHOD_NODE = 'methodNode';

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
    public const PREVIOUS_EXPRESSION = 'previousExpression';

    /**
     * @var string
     */
    public const CURRENT_EXPRESSION = 'currentExpression';

    /**
     * @var string
     */
    public const FILE_INFO = 'fileInfo';

    /**
     * @var string
     */
    public const START_TOKEN_POSITION = 'startTokenPos';
}
