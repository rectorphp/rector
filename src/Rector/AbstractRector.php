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
    private $expressionsToPrepend = [];

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    final public function beforeTraverse(array $nodes): array
    {
        $this->expressionsToPrepend = new SplObjectStorage;

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

    protected function prependNodeBehindNode(Expr $nodeToPrepend, Node $positionNode): void
    {
        /** @var Node $parentNode */
        $parentNode = $positionNode->getAttribute(Attribute::PARENT_NODE);
        if (! $parentNode instanceof Expression) {
            // validate?
            return;
        }

        $expressionToPrepend = new Expression($nodeToPrepend);

        if (isset($this->expressionsToPrepend[$parentNode])) {
            $this->expressionsToPrepend[$parentNode] = array_merge(
                $this->expressionsToPrepend[$parentNode],
                [$expressionToPrepend]
            );
        } else {
            $this->expressionsToPrepend[$parentNode] = [$expressionToPrepend];
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
                if (! isset($this->expressionsToPrepend[$node])) {
                    continue;
                }

                array_splice($nodes, $i + 1, 0, $this->expressionsToPrepend[$node]);

                unset($this->expressionsToPrepend[$node]);
            } elseif (isset($node->stmts)) {
                $node->stmts = $this->prependExpressionNodes($node->stmts);
            }
        }

        return $nodes;
    }
}
