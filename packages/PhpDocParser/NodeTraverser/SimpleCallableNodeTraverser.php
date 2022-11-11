<?php

declare (strict_types=1);
namespace Rector\PhpDocParser\NodeTraverser;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpDocParser\NodeVisitor\CallableNodeVisitor;
/**
 * @api
 */
final class SimpleCallableNodeTraverser
{
    /**
     * @param callable(Node $node): (int|Node|null) $callable
     * @param \PhpParser\Node|mixed[]|null $node
     */
    public function traverseNodesWithCallable($node, callable $callable) : void
    {
        if ($node === null) {
            return;
        }
        if ($node === []) {
            return;
        }
        $nodeTraverser = new NodeTraverser();
        $callableNodeVisitor = new CallableNodeVisitor($callable);
        $nodeTraverser->addVisitor($callableNodeVisitor);
        if ($this->shouldConnectParent($node)) {
            $nodeTraverser->addVisitor(new ParentConnectingVisitor());
        }
        $nodes = $node instanceof Node ? [$node] : $node;
        $nodeTraverser->traverse($nodes);
    }
    /**
     * @param \PhpParser\Node|mixed[] $node
     */
    private function shouldConnectParent($node) : bool
    {
        if ($node instanceof Node) {
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            return $parentNode instanceof Node;
        }
        // usage mostly by pass $node->stmts which parent node is the node
        return \true;
    }
}
