<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Maintainer;

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

    public function __construct(BetterStandardPrinter $betterStandardPrinter, ConstFetchMaintainer $constFetchMaintainer)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->constFetchMaintainer = $constFetchMaintainer;
    }

    /**
     * Matches:
     *
     * if ($value !== null) {
     *     return $value;
     * }
     */
    public function isIfNotNullReturnValue(If_ $ifNode): bool
    {
        if (count($ifNode->stmts) !== 1) {
            return false;
        }

        $insideIfNode = $ifNode->stmts[0];
        if (! $insideIfNode instanceof Return_) {
            return false;
        }

        /** @var Return_ $returnNode */
        $returnNode = $insideIfNode;

        if (! $ifNode->cond instanceof NotIdentical) {
            return false;
        }

        if ($this->betterStandardPrinter->areNodesEqual($ifNode->cond->left, $returnNode->expr)) {
            return $this->constFetchMaintainer->isNull($ifNode->cond->right);
        }

        if ($this->betterStandardPrinter->areNodesEqual($ifNode->cond->right, $returnNode->expr)) {
            return $this->constFetchMaintainer->isNull($ifNode->cond->left);
        }

        return false;
    }

    /**
     * Matches:
     *
     * if ($value === null) {
     *     return null;
     * }
     *
     * if ($value === 53;) {
     *     return 53;
     * }
     */
    public function isIfValueReturnValue(If_ $ifNode): bool
    {
        if (count($ifNode->stmts) !== 1) {
            return false;
        }

        $insideIfNode = $ifNode->stmts[0];
        if (! $insideIfNode instanceof Return_) {
            return false;
        }

        /** @var Return_ $returnNode */
        $returnNode = $insideIfNode;

        if (! $ifNode->cond instanceof Identical) {
            return false;
        }

        if ($this->betterStandardPrinter->areNodesEqual($ifNode->cond->left, $returnNode->expr)) {
            return true;
        }

        if ($this->betterStandardPrinter->areNodesEqual($ifNode->cond->right, $returnNode->expr)) {
            return true;
        }

        return false;
    }


}
