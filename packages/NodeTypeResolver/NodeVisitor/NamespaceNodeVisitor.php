<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitorAbstract;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class NamespaceNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var Use_[]
     */
    private $useNodes = [];

    public function __construct(
        private BetterNodeFinder $betterNodeFinder
    ) {
    }

    /**
     * @param Node[] $nodes
     * @return Node[]|null
     */
    public function beforeTraverse(array $nodes): ?array
    {
        // init basic use nodes for non-namespaced code
        /** @var Use_[] $uses */
        $uses = $this->betterNodeFinder->findInstanceOf($nodes, Use_::class);
        $this->useNodes = $uses;

        return null;
    }

    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof Namespace_) {
            /** @var Use_[] $uses */
            $uses = $this->betterNodeFinder->findInstanceOf($node, Use_::class);
            $this->useNodes = $uses;
        }

        $node->setAttribute(AttributeKey::USE_NODES, $this->useNodes);

        return $node;
    }
}
