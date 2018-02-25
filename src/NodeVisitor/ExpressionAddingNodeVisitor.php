<?php declare(strict_types=1);

namespace Rector\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use SplObjectStorage;

final class ExpressionAddingNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var SplObjectStorage
     */
    private $expressionsToAdd;

    public function setExpressionsToAdd(SplObjectStorage $expressionsToAdd): void
    {
        $this->expressionsToAdd = $expressionsToAdd;
    }

    /**
     * @return Node[]|Node
     */
    public function leaveNode(Node $node)
    {
        if (! isset($this->expressionsToAdd[$node])) {
            return $node;
        }

        $nodes = array_merge([$node], $this->expressionsToAdd[$node]);

        // $this->expressionsToAdd->detach($node);
        unset($this->expressionsToAdd[$node]);

        return $nodes;
    }
}
