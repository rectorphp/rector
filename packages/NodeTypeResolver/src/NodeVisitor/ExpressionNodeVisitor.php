<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\Attribute;

final class ExpressionNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var Expression|null
     */
    private $currentExpression;

    /**
     * @var Expression|null
     */
    private $previousExpression;

    /**
     * @param Node[] $nodes
     * @return Node[]|null
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->currentExpression = null;
        $this->previousExpression = null;

        return null;
    }

    /**
     * @return int|Node|void|null
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Expression && $this->currentExpression !== $node) {
            $this->previousExpression = $this->currentExpression;
            $this->currentExpression = $node;
        }

        $node->setAttribute(Attribute::PREVIOUS_EXPRESSION, $this->previousExpression);
        $node->setAttribute(Attribute::CURRENT_EXPRESSION, $this->currentExpression);
    }
}
