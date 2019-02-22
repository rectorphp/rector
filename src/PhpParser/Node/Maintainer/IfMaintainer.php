<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Maintainer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\PhpParser\Printer\BetterStandardPrinter;

final class IfMaintainer
{
    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var ConstFetchMaintainer
     */
    private $constFetchMaintainer;

    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        ConstFetchMaintainer $constFetchMaintainer
    ) {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->constFetchMaintainer = $constFetchMaintainer;
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

    private function matchComparedAndReturnedNode(NotIdentical $notIdentical, Return_ $returnNode): ?Expr
    {
        if ($this->betterStandardPrinter->areNodesEqual($notIdentical->left, $returnNode->expr)) {
            if ($this->constFetchMaintainer->isNull($notIdentical->right)) {
                return $notIdentical->left;
            }
        }

        if ($this->betterStandardPrinter->areNodesEqual($notIdentical->right, $returnNode->expr)) {
            if ($this->constFetchMaintainer->isNull($notIdentical->left)) {
                return $notIdentical->right;
            }
        }

        return null;
    }
}
