<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\Rector\RectorInterface;
use Rector\NodeVisitor\ExpressionPrependingNodeVisitor;

abstract class AbstractRector extends NodeVisitorAbstract implements RectorInterface
{
    /**
     * @var ExpressionPrependingNodeVisitor
     */
    private $expressionPrependingNodeVisitor;

    /**
     * Nasty magic, unable to do that in config autowire _instanceof calls.
     *
     * @required
     */
    public function setNodeVisitor(ExpressionPrependingNodeVisitor $expressionPrependingNodeVisitor): void
    {
        $this->expressionPrependingNodeVisitor = $expressionPrependingNodeVisitor;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    final public function beforeTraverse(array $nodes): array
    {
        $this->expressionPrependingNodeVisitor->clear();

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
        // prepend new expressions
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->expressionPrependingNodeVisitor);

        return $nodeTraverser->traverse($nodes);
    }

    protected function prependNodeAfterNode(Expr $nodeToPrepend, Node $positionNode): void
    {
        $this->expressionPrependingNodeVisitor->prependNodeAfterNode($nodeToPrepend, $positionNode);
    }
}
