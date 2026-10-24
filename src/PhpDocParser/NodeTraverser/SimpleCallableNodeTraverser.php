<?php

declare (strict_types=1);
namespace Rector\PhpDocParser\NodeTraverser;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use Rector\PhpDocParser\NodeVisitor\CallableNodeVisitor;
final class SimpleCallableNodeTraverser
{
    /**
     * @param Node|Node[]|null $node
     *
     * @param callable(Node $node): (int|Node|null|Node[]) $callable
     */
    public static function traverse($node, callable $callable): void
    {
        self::traverseNodesWithCallable($node, $callable);
    }
    /**
     * @param callable(Node $node): (int|Node|null|Node[]) $callable
     * @param Node|Node[]|null $node
     */
    public static function traverseNodesWithCallable($node, callable $callable): void
    {
        if ($node === null || $node === []) {
            return;
        }
        $callableNodeVisitor = new CallableNodeVisitor($callable);
        $nodeTraverser = new NodeTraverser($callableNodeVisitor);
        $nodes = $node instanceof Node ? [$node] : $node;
        $nodeTraverser->traverse($nodes);
    }
}
