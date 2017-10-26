<?php declare(strict_types=1);

namespace Rector\NodeVisitor;

use Doctrine\Common\Collections\Expr\Expression;
use PhpParser\Node;
use PhpParser\Node\Stmt\Nop;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeVisitor\Collector\ExpressionCollector;

/**
 * Removes expressions.
 */
final class ExpressionRemover extends NodeVisitorAbstract
{
    /**
     * @var ExpressionCollector
     */
    private $expressionCollector;

    public function __construct(ExpressionCollector $expressionCollector)
    {
        $this->expressionCollector = $expressionCollector;
    }

    public function enterNode(Node $node)
    {
        if (! $node instanceof Expression) {
            return null;
        }

        $expressionToRemove = $this->expressionCollector->getExpressionsToRemove();

        if (! in_array($node, $expressionToRemove)) {
            return null;
        }

        return new Nop;
    }
}
