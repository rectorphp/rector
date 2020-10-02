<?php

declare(strict_types=1);

namespace Rector\Core\Rector\AbstractRector;

use PhpParser\Node;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait CallableNodeTraverserTrait
{
    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @required
     */
    public function autowireCallableNodeTraverserTrait(CallableNodeTraverser $callableNodeTraverser): void
    {
        $this->callableNodeTraverser = $callableNodeTraverser;
    }

    /**
     * @param Node|Node[] $nodes
     */
    public function traverseNodesWithCallable($nodes, callable $callable): void
    {
        $this->callableNodeTraverser->traverseNodesWithCallable($nodes, $callable);
    }
}
