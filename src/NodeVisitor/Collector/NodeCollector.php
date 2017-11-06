<?php declare(strict_types=1);

namespace Rector\NodeVisitor\Collector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeTraverserQueue\BetterNodeFinder;

final class NodeCollector
{
    /**
     * @var Expression[]|Node[]
     */
    private $nodesToRemove = [];

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }

    public function addNodeToRemove(Node $node): void
    {
        $this->nodesToRemove[] = $node;
    }

    public function addNodeToRemoveExpression(Node $node): void
    {
        $expressionNode = $this->betterNodeFinder->findFirstAncestorInstanceOf($node, Expression::class);

        if ($expressionNode) {
            $this->nodesToRemove[] = $expressionNode;
        }
    }

    /**
     * @return Expression[]|Node[]
     */
    public function getNodesToRemove(): array
    {
        return $this->nodesToRemove;
    }
}
