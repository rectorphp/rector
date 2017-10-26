<?php declare(strict_types=1);

namespace Rector\NodeVisitor\Collector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeFinder;

final class ExpressionCollector
{
    /**
     * @var Expression[]
     */
    private $expressionsToRemove = [];
    /**
     * @var NodeFinder
     */
    private $nodeFinder;

    public function __construct(NodeFinder $nodeFinder)
    {
        $this->nodeFinder = $nodeFinder;
    }

    public function addNodeToRemove(Node $node): void
    {


        dump($node);
        die;
    }

    public function addExpressionToRemove(Expression $expression): void
    {
        $this->expressionsToRemove[] = $expression;
    }

    /**
     * @return Expression[]
     */
    public function getExpressionsToRemove(): array
    {
        return $this->expressionsToRemove;
    }
}
