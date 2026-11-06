<?php

declare (strict_types=1);
namespace Rector\Php84\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use Rector\PhpParser\Node\BetterNodeFinder;
/**
 * Arrow functions capture outer variables by value, so writes inside the conditional would be lost
 */
final class ExprModifiedInConditionalAnalyzer
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function isModified(Expr $expr): bool
    {
        return $this->betterNodeFinder->hasInstancesOf($expr, [Assign::class, AssignOp::class, AssignRef::class, PreInc::class, PreDec::class, PostInc::class, PostDec::class]);
    }
}
