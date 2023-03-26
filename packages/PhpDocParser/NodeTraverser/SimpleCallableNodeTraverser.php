<?php

declare (strict_types=1);
namespace Rector\PhpDocParser\NodeTraverser;

use PhpParser\Node;
use PhpParser\NodeTraverser;
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
        $nodes = $node instanceof Node ? [$node] : $node;
        $nodeTraverser->traverse($nodes);
    }
}
