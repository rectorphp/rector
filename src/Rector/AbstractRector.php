<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\Rector\RectorInterface;
use Rector\Node\Attribute;
use SplObjectStorage;

abstract class AbstractRector extends NodeVisitorAbstract implements RectorInterface
{
    /**
     * @var bool
     */
    protected $shouldRemoveNode = false;

    /**
     * @var SplObjectStorage|Expression[]
     */
    private $expressionsToPrependBefore = [];

    /**
     * @var SplObjectStorage|Expression[]
     */
    private $expressionsToPrependAfter = [];

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    final public function beforeTraverse(array $nodes): array
    {
        $this->expressionsToPrependBefore = new SplObjectStorage;
        $this->expressionsToPrependAfter = new SplObjectStorage;

        return $nodes;
    }

    /**
     * @return null|int|Node
     */
    final public function enterNode(Node $node)
    {
        if ($this->isCandidate($node)) {
            if ($newNode = $this->refactor($node)) {
                return $newNode;
            }

            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        return null;
    }

    /**
     * @return null|int|Node
     */
    final public function leaveNode(Node $node)
    {
        if ($this->shouldRemoveNode) {
            $this->shouldRemoveNode = false;

            return new Nop;
        }

        return null;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function afterTraverse(array $nodes): array
    {
        return $this->prependExpressionNodes($nodes);
    }

    protected function prependNodeAfterNode(Expr $nodeToPrepend, Node $positionNode): void
    {
        /** @var Node $parentNode */
        $parentNode = $positionNode->getAttribute(Attribute::PARENT_NODE);
        if (! $parentNode instanceof Expression) {
            // validate?
            return;
        }

        $expressionToPrepend = new Expression($nodeToPrepend);

        if (isset($this->expressionsToPrependAfter[$parentNode])) {
            $this->expressionsToPrependAfter[$parentNode] = array_merge(
                $this->expressionsToPrependAfter[$parentNode],
                [$expressionToPrepend]
            );
        } else {
            $this->expressionsToPrependAfter[$parentNode] = [$expressionToPrepend];
        }
    }

    protected function prependNodeBeforeNode(Expr $nodeToPrepend, Node $positionNode): void
    {
        /** @var Node $parentNode */
        $parentNode = $positionNode->getAttribute(Attribute::PARENT_NODE);
        if (! $parentNode instanceof Expression) {
            // validate?
            return;
        }

        $expressionToPrepend = new Expression($nodeToPrepend);

        if (isset($this->expressionsToPrependBefore[$parentNode])) {
            $this->expressionsToPrependBefore[$parentNode] = array_merge(
                $this->expressionsToPrependBefore[$parentNode],
                [$expressionToPrepend]
            );
        } else {
            $this->expressionsToPrependBefore[$parentNode] = [$expressionToPrepend];
        }
    }

    /**
     * @param Node[] $nodes
     * @return Node[] array
     */
    private function prependExpressionNodes(array $nodes): array
    {
        foreach ($nodes as $i => $node) {
            if ($node instanceof Expression) {
                $nodes = $this->prependNodesAfterAndBeforeExpression($nodes, $node, $i);
            } elseif (isset($node->stmts)) {
                $node->stmts = $this->prependExpressionNodes($node->stmts);
            }
        }

        return $nodes;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    private function prependNodesAfterAndBeforeExpression(array $nodes, Node $node, int $i): array
    {
        if (isset($this->expressionsToPrependBefore[$node])) {
            array_splice($nodes, $i, 0, $this->expressionsToPrependBefore[$node]);

            unset($this->expressionsToPrependBefore[$node]);
        }

        if (isset($this->expressionsToPrependAfter[$node])) {
            array_splice($nodes, $i + 1, 0, $this->expressionsToPrependAfter[$node]);

            unset($this->expressionsToPrependAfter[$node]);
        }

        return $nodes;
    }
}
