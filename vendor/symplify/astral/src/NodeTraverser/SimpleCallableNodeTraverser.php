<?php

declare (strict_types=1);
namespace RectorPrefix20211231\Symplify\Astral\NodeTraverser;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use RectorPrefix20211231\Symplify\Astral\NodeVisitor\CallableNodeVisitor;
/**
 * @api
 */
final class SimpleCallableNodeTraverser
{
    /**
     * @param mixed[]|\PhpParser\Node|null $nodes
     */
    public function traverseNodesWithCallable($nodes, callable $callable) : void
    {
        if ($nodes === null) {
            return;
        }
        if ($nodes === []) {
            return;
        }
        if (!\is_array($nodes)) {
            $nodes = [$nodes];
        }
        $nodeTraverser = new \PhpParser\NodeTraverser();
        $callableNodeVisitor = new \RectorPrefix20211231\Symplify\Astral\NodeVisitor\CallableNodeVisitor($callable);
        $nodeTraverser->addVisitor($callableNodeVisitor);
        $nodeTraverser->traverse($nodes);
    }
}
