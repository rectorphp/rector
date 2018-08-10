<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Node;

final class MetadataAttribute
{
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
     * @var string
     */
    public const METHOD_CALL_NAME = 'methodCallName';
}
