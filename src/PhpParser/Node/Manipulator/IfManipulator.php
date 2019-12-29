<?php

declare(strict_types=1);

namespace Rector\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\While_;
use PhpParser\NodeTraverser;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\PhpParser\Printer\BetterStandardPrinter;

final class IfManipulator
{
    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var ConstFetchManipulator
     */
    private $constFetchManipulator;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        ConstFetchManipulator $constFetchManipulator,
        CallableNodeTraverser $callableNodeTraverser
    ) {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->constFetchManipulator = $constFetchManipulator;
        $this->callableNodeTraverser = $callableNodeTraverser;
    }

    /**
     * Matches:
     *
     * if (<$value> !== null) {
     *     return $value;
     * }
     */
    public function matchIfNotNullReturnValue(If_ $ifNode): ?Expr
    {
        if (count($ifNode->stmts) !== 1) {
            return null;
        }

        $insideIfNode = $ifNode->stmts[0];
        if (! $insideIfNode instanceof Return_) {
            return null;
        }

        /** @var Return_ $returnNode */
        $returnNode = $insideIfNode;
        if (! $ifNode->cond instanceof NotIdentical) {
            return null;
        }

        return $this->matchComparedAndReturnedNode($ifNode->cond, $returnNode);
    }

    /**
     * Matches:
     *
     * if (<$value> === null) {
     *     return null;
     * }
     *
     * if (<$value> === 53;) {
     *     return 53;
     * }
     */
    public function matchIfValueReturnValue(If_ $ifNode): ?Expr
    {
        if (count($ifNode->stmts) !== 1) {
            return null;
        }

        $insideIfNode = $ifNode->stmts[0];
        if (! $insideIfNode instanceof Return_) {
            return null;
        }

        /** @var Return_ $returnNode */
        $returnNode = $insideIfNode;

        if (! $ifNode->cond instanceof Identical) {
            return null;
        }

        if ($this->betterStandardPrinter->areNodesEqual($ifNode->cond->left, $returnNode->expr)) {
            return $ifNode->cond->right;
        }

        if ($this->betterStandardPrinter->areNodesEqual($ifNode->cond->right, $returnNode->expr)) {
            return $ifNode->cond->left;
        }

        return null;
    }

    public function isWithElseAlwaysReturnValue(If_ $if): bool
    {
        if (! $this->isAlwaysReturnValue((array) $if->stmts)) {
            return false;
        }

        foreach ($if->elseifs as $elseif) {
            if (! $this->isAlwaysReturnValue((array) $elseif->stmts)) {
                return false;
            }
        }

        if ($if->else === null) {
            return false;
        }

        return $this->isAlwaysReturnValue((array) $if->else->stmts);
    }

    private function matchComparedAndReturnedNode(NotIdentical $notIdentical, Return_ $returnNode): ?Expr
    {
        if ($this->betterStandardPrinter->areNodesEqual($notIdentical->left, $returnNode->expr)) {
            if ($this->constFetchManipulator->isNull($notIdentical->right)) {
                return $notIdentical->left;
            }
        }

        if ($this->betterStandardPrinter->areNodesEqual($notIdentical->right, $returnNode->expr)) {
            if ($this->constFetchManipulator->isNull($notIdentical->left)) {
                return $notIdentical->right;
            }
        }

        return null;
    }

    private function isAlwaysReturnValue(array $stmts): bool
    {
        $isAlwaysReturnValue = false;

        $this->callableNodeTraverser->traverseNodesWithCallable($stmts, function (Node $node) use (
            &$isAlwaysReturnValue
        ) {
            if ($this->isScopeChangingNode($node)) {
                $isAlwaysReturnValue = false;

                return NodeTraverser::STOP_TRAVERSAL;
            }

            if ($node instanceof Return_) {
                if ($node->expr === null) {
                    $isAlwaysReturnValue = false;

                    return NodeTraverser::STOP_TRAVERSAL;
                }

                $isAlwaysReturnValue = true;
            }
        });

        return $isAlwaysReturnValue;
    }

    private function isScopeChangingNode(Node $node): bool
    {
        $scopeChangingNodes = [Do_::class, While_::class, If_::class, Else_::class];

        foreach ($scopeChangingNodes as $scopeChangingNode) {
            if (! is_a($node, $scopeChangingNode, true)) {
                continue;
            }

            return true;
        }

        return false;
    }
}
