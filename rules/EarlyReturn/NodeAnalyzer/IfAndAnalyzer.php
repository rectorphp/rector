<?php

declare (strict_types=1);
namespace Rector\EarlyReturn\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
final class IfAndAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
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
        foreach ($ifExprs as $ifExpr) {
            $isExprFoundInReturn = (bool) $this->betterNodeFinder->findFirst($return->expr, function (Node $node) use($ifExpr) : bool {
                return $this->nodeComparator->areNodesEqual($node, $ifExpr);
            });
            if ($isExprFoundInReturn) {
                return \true;
            }
        }
        return \false;
    }
}
