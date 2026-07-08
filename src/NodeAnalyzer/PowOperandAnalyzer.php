<?php

declare (strict_types=1);
namespace Rector\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\BitwiseNot;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\ErrorSuppress;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Print_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\UnaryPlus;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Expr\YieldFrom;
/**
 * The ** operator binds tighter than most operators, so an operand that binds looser must be
 * wrapped in parentheses when placed next to it, e.g. pow(~3, 4) must become (~3) ** 4.
 * @see \Rector\Tests\NodeAnalyzer\PowOperandAnalyzerTest
 */
final class PowOperandAnalyzer
{
    /**
     * Operators that bind looser than ** on the left-hand side, so $operand ** $y would be
     * misparsed without parentheses. Unary operators and casts are legal bare on the right-hand
     * side of ** (2 ** -3), so they only need wrapping when used as the left operand.
     */
    public function isLowerPrecedenceAsLeftOperand(Expr $expr): bool
    {
        // a plain Assign as left operand is already parenthesized by BetterStandardPrinter,
        // wrapping it again here would produce a double set of parentheses
        if ($expr instanceof Assign) {
            return \false;
        }
        if ($expr instanceof UnaryMinus || $expr instanceof UnaryPlus || $expr instanceof BitwiseNot || $expr instanceof BooleanNot || $expr instanceof ErrorSuppress || $expr instanceof Cast || $expr instanceof Instanceof_) {
            return \true;
        }
        return $this->isLowerPrecedenceAsRightOperand($expr);
    }
    /**
     * Operators that would otherwise swallow the ** expression on either side, e.g.
     * pow(2, $a ? 3 : 4) must become 2 ** ($a ? 3 : 4), not 2 ** $a ? 3 : 4.
     */
    public function isLowerPrecedenceAsRightOperand(Expr $expr): bool
    {
        return $expr instanceof Ternary || $expr instanceof Assign || $expr instanceof AssignRef || $expr instanceof AssignOp || $expr instanceof Print_ || $expr instanceof Yield_ || $expr instanceof YieldFrom;
    }
}
