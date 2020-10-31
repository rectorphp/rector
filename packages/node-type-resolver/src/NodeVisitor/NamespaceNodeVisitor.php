<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitorAbstract;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class NamespaceNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var Declare_[]
     */
    private $declares = [];

    /**
     * @var Use_[]
     */
    private $useNodes = [];

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var string|null
     */
    private $namespaceName;

    /**
     * @var Namespace_|null
     */
    private $namespace;

    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]|null
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->namespaceName = null;
        $this->namespace = null;

        // init basic use nodes for non-namespaced code
        /** @var Use_[] $uses */
        $uses = $this->betterNodeFinder->findInstanceOf($nodes, Use_::class);
        $this->useNodes = $uses;

        /** @var Declare_[] $declares */
        $declares = $this->betterNodeFinder->findInstanceOf($nodes, Declare_::class);
        $this->declares = $declares;

        return null;
    }

    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof Namespace_) {
            $this->namespaceName = $node->name !== null ? $node->name->toString() : null;
            $this->namespace = $node;

            /** @var Use_[] $uses */
            $uses = $this->betterNodeFinder->findInstanceOf($node, Use_::class);
            $this->useNodes = $uses;
        }

        $node->setAttribute(AttributeKey::NAMESPACE_NAME, $this->namespaceName);
        $node->setAttribute(AttributeKey::NAMESPACE_NODE, $this->namespace);
        $node->setAttribute(AttributeKey::USE_NODES, $this->useNodes);
        $node->setAttribute(AttributeKey::DECLARES, $this->declares);

        return $node;
    }
}
