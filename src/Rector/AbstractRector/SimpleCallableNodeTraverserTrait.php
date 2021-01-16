<?php

declare(strict_types=1);

namespace Rector\Core\Rector\AbstractRector;

use PhpParser\Node;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait SimpleCallableNodeTraverserTrait
{
    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    /**
     * @required
     */
    public function autowireSimpleCallableNodeTraverserTrait(
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser
    ): void {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }

    /**
     * @param Node|Node[] $nodes
     */
    public function traverseNodesWithCallable($nodes, callable $callable): void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($nodes, $callable);
    }
}
