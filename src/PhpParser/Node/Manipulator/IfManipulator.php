<?php

declare(strict_types=1);

namespace Rector\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\Node\Stmt\While_;
use PhpParser\NodeTraverser;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\PhpParser\Printer\BetterStandardPrinter;

final class IfManipulator
{
    /**
     * @var string[]
     */
    private const ALLOWED_BREAKING_NODE_TYPES = [Return_::class, Throw_::class, Continue_::class];

    /**
     * @var string[]
     */
    private const SCOPE_CHANGING_NODE_TYPES = [Do_::class, While_::class, If_::class, Else_::class];

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

    public function isEarlyElse(If_ $if): bool
    {
        if (! $this->isAlwaysAllowedType((array) $if->stmts, self::ALLOWED_BREAKING_NODE_TYPES)) {
            return false;
        }

        foreach ($if->elseifs as $elseif) {
            if (! $this->isAlwaysAllowedType((array) $elseif->stmts, self::ALLOWED_BREAKING_NODE_TYPES)) {
                return false;
            }
        }

        return $if->else !== null;
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

    /**
     * @param Node[] $stmts
     * @param string[] $allowedTypes
     */
    private function isAlwaysAllowedType(array $stmts, array $allowedTypes): bool
    {
        $isAlwaysReturnValue = false;

        $this->callableNodeTraverser->traverseNodesWithCallable($stmts, function (Node $node) use (
            &$isAlwaysReturnValue,
            $allowedTypes
        ) {
            if ($this->isScopeChangingNode($node)) {
                $isAlwaysReturnValue = false;

                return NodeTraverser::STOP_TRAVERSAL;
            }

            foreach ($allowedTypes as $allowedType) {
                if (is_a($node, $allowedType, true)) {
                    if ($allowedType === Return_::class) {
                        if ($node->expr === null) {
                            $isAlwaysReturnValue = false;

                            return NodeTraverser::STOP_TRAVERSAL;
                        }
                    }

                    $isAlwaysReturnValue = true;
                }
            }

            return null;
        });

        return $isAlwaysReturnValue;
    }

    private function isScopeChangingNode(Node $node): bool
    {
        foreach (self::SCOPE_CHANGING_NODE_TYPES as $scopeChangingNode) {
            if (! is_a($node, $scopeChangingNode, true)) {
                continue;
            }

            return true;
        }

        return false;
    }
}
