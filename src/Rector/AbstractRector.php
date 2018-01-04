<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\Rector\RectorInterface;
use Rector\NodeChanger\ExpressionPrepender;

abstract class AbstractRector extends NodeVisitorAbstract implements RectorInterface
{
    /**
     * @var ExpressionPrepender
     */
    private $expressionPrepender;

    /**
     * Nasty magic, unable to do that in config autowire _instanceof calls.
     *
     * @required
     */
    public function setExpressionPrepender(ExpressionPrepender $expressionPrepender): void
    {
        $this->expressionPrepender = $expressionPrepender;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    final public function beforeTraverse(array $nodes): array
    {
        $this->expressionPrepender->clear();

        return $nodes;
    }

    /**
     * @return null|int|Node
     */
    final public function enterNode(Node $node)
    {
        if ($this->isCandidate($node)) {
            $newNode = $this->refactor($node);
            if ($newNode !== null) {
                return $newNode;
            }

            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        return null;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function afterTraverse(array $nodes): array
    {
        return $this->expressionPrepender->prependExpressionsToNodes($nodes);
    }

    protected function prependNodeAfterNode(Expr $nodeToPrepend, Node $positionNode): void
    {
        $this->expressionPrepender->prependNodeAfterNode($nodeToPrepend, $positionNode);
    }
}
