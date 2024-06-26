<?php

declare (strict_types=1);
namespace Rector\EarlyReturn\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\PhpParser\Comparing\NodeComparator;
use Rector\PhpParser\Node\BetterNodeFinder;
/**
 * @deprecated Since 1.1.2, as related rule creates inverted conditions and makes code much less readable.
 */
final class IfAndAnalyzer
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeComparator $nodeComparator)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
    }
    public function isIfAndWithInstanceof(BooleanAnd $booleanAnd) : bool
    {
        if (!$booleanAnd->left instanceof Instanceof_) {
            return \false;
        }
        // only one instanceof check
        return !$booleanAnd->right instanceof Instanceof_;
    }
    public function isIfStmtExprUsedInNextReturn(If_ $if, Return_ $return) : bool
    {
        if (!$return->expr instanceof Expr) {
            return \false;
        }
        $ifExprs = $this->betterNodeFinder->findInstanceOf($if->stmts, Expr::class);
        return (bool) $this->betterNodeFinder->findFirst($return->expr, function (Node $node) use($ifExprs) : bool {
            return $this->nodeComparator->isNodeEqual($node, $ifExprs);
        });
    }
}
