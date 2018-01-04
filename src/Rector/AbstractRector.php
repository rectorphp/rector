<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\Rector\RectorInterface;
use Rector\NodeChanger\ExpressionPrepender;
use Rector\NodeChanger\PropertyAdder;

abstract class AbstractRector extends NodeVisitorAbstract implements RectorInterface
{
    /**
     * @var ExpressionPrepender
     */
    private $expressionPrepender;

    /**
     * @var \Rector\NodeChanger\PropertyAdder
     */
    private $propertyAdder;

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
     * Nasty magic, unable to do that in config autowire _instanceof calls.
     *
     * @required
     */
    public function setPropertyToClassAdder(PropertyAdder $propertyAdder): void
    {
        $this->propertyAdder = $propertyAdder;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    final public function beforeTraverse(array $nodes): array
    {
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
        $nodes = $this->expressionPrepender->prependExpressionsToNodes($nodes);

        $nodes = $this->propertyAdder->addPropertiesToNodes($nodes);

        return $nodes;
    }


    protected function prependNodeAfterNode(Expr $nodeToPrepend, Node $positionNode): void
    {
        $this->expressionPrepender->prependNodeAfterNode($nodeToPrepend, $positionNode);
    }
}
