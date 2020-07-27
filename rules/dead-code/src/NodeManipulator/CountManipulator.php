<?php

declare(strict_types=1);

namespace Rector\DeadCode\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\LNumber;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeNameResolver\NodeNameResolver;

final class CountManipulator
{
    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(BetterStandardPrinter $betterStandardPrinter, NodeNameResolver $nodeNameResolver)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function isCounterHigherThanOne(Node $node, Expr $expr): bool
    {
        // e.g. count($values) > 0
        if ($node instanceof Greater) {
            return $this->processGreater($node, $expr);
        }

        // e.g. count($values) >= 1
        if ($node instanceof GreaterOrEqual) {
            return $this->processGreaterOrEqual($node, $expr);
        }

        // e.g. 0 < count($values)
        if ($node instanceof Smaller) {
            return $this->processSmaller($node, $expr);
        }

        // e.g. 1 <= count($values)
        if ($node instanceof SmallerOrEqual) {
            return $this->processSmallerOrEqual($node, $expr);
        }

        return false;
    }

    private function processGreater(Greater $greater, Expr $expr): bool
    {
        if (! $this->isNumber($greater->right, 0)) {
            return false;
        }

        return $this->isCountWithExpression($greater->left, $expr);
    }

    private function processGreaterOrEqual(GreaterOrEqual $greaterOrEqual, Expr $expr): bool
    {
        if (! $this->isNumber($greaterOrEqual->right, 1)) {
            return false;
        }

        return $this->isCountWithExpression($greaterOrEqual->left, $expr);
    }

    private function processSmaller(Smaller $smaller, Expr $expr): bool
    {
        if (! $this->isNumber($smaller->left, 0)) {
            return false;
        }

        return $this->isCountWithExpression($smaller->right, $expr);
    }

    private function processSmallerOrEqual(SmallerOrEqual $smallerOrEqual, Expr $expr): bool
    {
        if (! $this->isNumber($smallerOrEqual->left, 1)) {
            return false;
        }

        return $this->isCountWithExpression($smallerOrEqual->right, $expr);
    }

    private function isNumber(Node $node, int $value): bool
    {
        if (! $node instanceof LNumber) {
            return false;
        }

        return $node->value === $value;
    }

    private function isCountWithExpression(Node $node, Expr $expr): bool
    {
        if (! $node instanceof FuncCall) {
            return false;
        }

        if (! $this->nodeNameResolver->isName($node, 'count')) {
            return false;
        }

        $countedExpr = $node->args[0]->value;

        return $this->betterStandardPrinter->areNodesEqual($countedExpr, $expr);
    }
}
