<?php

declare (strict_types=1);
namespace Rector\DeadCode;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use Rector\Core\NodeAnalyzer\ParamAnalyzer;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
final class UselessIfCondBeforeForeachDetector
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ParamAnalyzer
     */
    private $paramAnalyzer;
    public function __construct(NodeComparator $nodeComparator, BetterNodeFinder $betterNodeFinder, ParamAnalyzer $paramAnalyzer)
    {
        $this->nodeComparator = $nodeComparator;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->paramAnalyzer = $paramAnalyzer;
    }
    /**
     * Matches:
     * empty($values)
     */
    public function isMatchingEmptyAndForeachedExpr(If_ $if, Expr $foreachExpr) : bool
    {
        if (!$if->cond instanceof Empty_) {
            return \false;
        }
        /** @var Empty_ $empty */
        $empty = $if->cond;
        if (!$this->nodeComparator->areNodesEqual($empty->expr, $foreachExpr)) {
            return \false;
        }
        if ($if->stmts === []) {
            return \true;
        }
        if (\count($if->stmts) !== 1) {
            return \false;
        }
        $stmt = $if->stmts[0];
        return $stmt instanceof Return_ && !$stmt->expr instanceof Expr;
    }
    /**
     * Matches:
     * !empty($values)
     */
    public function isMatchingNotEmpty(If_ $if, Expr $foreachExpr, Scope $scope) : bool
    {
        $cond = $if->cond;
        if (!$cond instanceof BooleanNot) {
            return \false;
        }
        if (!$cond->expr instanceof Empty_) {
            return \false;
        }
        /** @var Empty_ $empty */
        $empty = $cond->expr;
        return $this->areCondExprAndForeachExprSame($empty, $foreachExpr, $scope);
    }
    /**
     * Matches:
     * $values !== []
     * $values != []
     * [] !== $values
     * [] != $values
     */
    public function isMatchingNotIdenticalEmptyArray(If_ $if, Expr $foreachExpr) : bool
    {
        if (!$if->cond instanceof NotIdentical && !$if->cond instanceof NotEqual) {
            return \false;
        }
        /** @var NotIdentical|NotEqual $notIdentical */
        $notIdentical = $if->cond;
        return $this->isMatchingNotBinaryOp($notIdentical, $foreachExpr);
    }
    private function fromPreviousParam(Expr $expr) : ?Param
    {
        return $this->betterNodeFinder->findFirstPrevious($expr, function (Node $node) use($expr) : bool {
            if (!$node instanceof Param) {
                return \false;
            }
            if (!$node->var instanceof Variable) {
                return \false;
            }
            return $this->nodeComparator->areNodesEqual($node->var, $expr);
        });
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\NotIdentical|\PhpParser\Node\Expr\BinaryOp\NotEqual $binaryOp
     */
    private function isMatchingNotBinaryOp($binaryOp, Expr $foreachExpr) : bool
    {
        if ($this->isEmptyArrayAndForeachedVariable($binaryOp->left, $binaryOp->right, $foreachExpr)) {
            return \true;
        }
        return $this->isEmptyArrayAndForeachedVariable($binaryOp->right, $binaryOp->left, $foreachExpr);
    }
    private function isEmptyArrayAndForeachedVariable(Expr $leftExpr, Expr $rightExpr, Expr $foreachExpr) : bool
    {
        if (!$this->isEmptyArray($leftExpr)) {
            return \false;
        }
        return $this->nodeComparator->areNodesEqual($foreachExpr, $rightExpr);
    }
    private function isEmptyArray(Expr $expr) : bool
    {
        if (!$expr instanceof Array_) {
            return \false;
        }
        return $expr->items === [];
    }
    private function areCondExprAndForeachExprSame(Empty_ $empty, Expr $foreachExpr, Scope $scope) : bool
    {
        if (!$this->nodeComparator->areNodesEqual($empty->expr, $foreachExpr)) {
            return \false;
        }
        // is array though?
        $arrayType = $scope->getType($empty->expr);
        if (!$arrayType->isArray()->yes()) {
            return \false;
        }
        $previousParam = $this->fromPreviousParam($foreachExpr);
        if (!$previousParam instanceof Param) {
            return \true;
        }
        if ($this->paramAnalyzer->isNullable($previousParam)) {
            return \false;
        }
        return !$this->paramAnalyzer->hasDefaultNull($previousParam);
    }
}
