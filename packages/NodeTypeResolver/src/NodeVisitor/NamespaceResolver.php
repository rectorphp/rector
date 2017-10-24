<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitorAbstract;
use Rector\Node\Attribute;

final class NamespaceResolver extends NodeVisitorAbstract
{
    /**
     * @var string|null
     */
    private $namespaceName;

    /**
     * @var Namespace_|null
     */
    private $namespaceNode;

    /**
     * @var NodeFinder
     */
    private $nodeFinder;

    /**
     * @var Use_[]
     */
    private $useNodes = [];

    public function __construct(NodeFinder $nodeFinder)
    {
        $this->nodeFinder = $nodeFinder;
    }

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): void
    {
        $this->namespaceName = null;
        $this->namespaceNode = null;
    }

    public function enterNode(Node $node): void
    {
        if ($node instanceof Namespace_) {
            $this->namespaceName = $node->name ? $node->name->toString() : null;
            $this->namespaceNode = $node;
            $this->useNodes = $this->nodeFinder->findInstanceOf($node, Use_::class);
        }

        $node->setAttribute(Attribute::NAMESPACE_NAME, $this->namespaceName);
        $node->setAttribute(Attribute::NAMESPACE_NODE, $this->namespaceNode);
        $node->setAttribute(Attribute::USE_NODES, $this->useNodes);
    }
}
