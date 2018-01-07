<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\Rector\RectorInterface;
use Rector\NodeChanger\ExpressionAdder;
use Rector\NodeChanger\PropertyAdder;

abstract class AbstractRector extends NodeVisitorAbstract implements RectorInterface
{
    /**
     * @var ExpressionAdder
     */
    private $expressionAdder;

    /**
     * @var PropertyAdder
     */
    private $propertyAdder;

    /**
     * Nasty magic, unable to do that in config autowire _instanceof calls.
     *
     * @required
     */
    public function setExpressionAdder(ExpressionAdder $expressionAdder): void
    {
        $this->expressionAdder = $expressionAdder;
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
        $nodes = $this->expressionAdder->addExpressionsToNodes($nodes);

        $nodes = $this->propertyAdder->addPropertiesToNodes($nodes);

        return $nodes;
    }

    protected function addNodeAfterNode(Expr $newNode, Node $positionNode): void
    {
        $this->expressionAdder->addNodeAfterNode($newNode, $positionNode);
    }
}
