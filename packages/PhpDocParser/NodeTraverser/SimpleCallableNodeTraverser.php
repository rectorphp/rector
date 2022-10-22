<?php

declare (strict_types=1);
namespace Rector\PhpDocParser\NodeTraverser;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpDocParser\NodeVisitor\CallableNodeVisitor;
/**
 * @api
 */
final class SimpleCallableNodeTraverser
{
    /**
     * @param callable(Node $node): (int|Node|null) $callable
     * @param \PhpParser\Node|mixed[]|null $nodes
     */
    public function traverseNodesWithCallable($nodes, callable $callable) : void
    {
        if ($nodes === null) {
            return;
        }
        if ($nodes === []) {
            return;
        }
        if (!\is_array($nodes)) {
            $nodes = [$nodes];
        }
        $nodeTraverser = new NodeTraverser();
        $callableNodeVisitor = new CallableNodeVisitor($callable);
        $nodeTraverser->addVisitor($callableNodeVisitor);
        $this->mirrorParentReturnArrowFunction($nodes);
        $nodeTraverser->addVisitor(new ParentConnectingVisitor());
        $nodeTraverser->traverse($nodes);
    }
    /**
     * The ArrowFunction when call ->getStmts(), it returns
     *
     *      return [new Node\Stmt\Return_($this->expr)]
     *
     * The $expr property has parent Node ArrowFunction, but not the Return_ stmt,
     * so need to mirror $expr parent Node into Return_ stmt
     *
     * @param Node[] $nodes
     */
    private function mirrorParentReturnArrowFunction(array $nodes) : void
    {
        foreach ($nodes as $node) {
            if (!$node instanceof Return_) {
                continue;
            }
            if (!$node->expr instanceof Expr) {
                continue;
            }
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode instanceof Node) {
                continue;
            }
            $exprParent = $node->expr->getAttribute(AttributeKey::PARENT_NODE);
            if ($exprParent instanceof ArrowFunction) {
                $node->setAttribute(AttributeKey::PARENT_NODE, $exprParent);
            }
        }
    }
}
