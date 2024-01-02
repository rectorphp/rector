<?php

declare (strict_types=1);
namespace Rector\Php70\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\Ternary;
use Rector\Php70\Enum\BattleshipCompareOrder;
use Rector\Php70\ValueObject\ComparedExprs;
use Rector\PhpParser\Comparing\NodeComparator;
use Rector\PhpParser\Node\Value\ValueResolver;
final class BattleshipTernaryAnalyzer
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(NodeComparator $nodeComparator, ValueResolver $valueResolver)
    {
        $this->nodeComparator = $nodeComparator;
        $this->valueResolver = $valueResolver;
    }
    /**
     * @return BattleshipCompareOrder::*|null
     */
    public function isGreaterLowerCompareReturnOneAndMinusOne(Ternary $ternary, ComparedExprs $comparedExprs) : ?string
    {
        if ($ternary->cond instanceof Greater) {
            return $this->evaluateGreater($ternary->cond, $ternary, $comparedExprs);
        }
        if ($ternary->cond instanceof Smaller) {
            return $this->evaluateSmaller($ternary->cond, $ternary, $comparedExprs);
        }
        return null;
    }
    /**
     * We look for:
     *
     * $firstValue > $secondValue ? 1 : -1
     *
     * @return BattleshipCompareOrder::*|null
     */
    private function evaluateGreater(Greater $greater, Ternary $ternary, ComparedExprs $comparedExprs) : ?string
    {
        if (!$ternary->if instanceof Expr) {
            return null;
        }
        if ($this->nodeComparator->areNodesEqual($greater->left, $comparedExprs->getFirstExpr()) && $this->nodeComparator->areNodesEqual($greater->right, $comparedExprs->getSecondExpr())) {
            return $this->evaluateTernaryDesc($ternary);
        }
        if (!$this->nodeComparator->areNodesEqual($greater->right, $comparedExprs->getFirstExpr())) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($greater->left, $comparedExprs->getSecondExpr())) {
            return null;
        }
        return $this->evaluateTernaryAsc($ternary);
    }
    /**
     * We look for:
     *
     * $firstValue < $secondValue ? -1 : 1
     *
     * @return BattleshipCompareOrder::*|null
     */
    private function evaluateSmaller(Smaller $smaller, Ternary $ternary, ComparedExprs $comparedExprs) : ?string
    {
        if (!$ternary->if instanceof Expr) {
            return null;
        }
        if ($this->nodeComparator->areNodesEqual($smaller->left, $comparedExprs->getFirstExpr()) && $this->nodeComparator->areNodesEqual($smaller->right, $comparedExprs->getSecondExpr())) {
            return $this->evaluateTernaryAsc($ternary);
        }
        if (!$this->nodeComparator->areNodesEqual($smaller->right, $comparedExprs->getFirstExpr())) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($smaller->left, $comparedExprs->getSecondExpr())) {
            return null;
        }
        return $this->evaluateTernaryDesc($ternary);
    }
    private function isValueOneAndMinusOne(Expr $firstExpr, Expr $seconcExpr) : bool
    {
        if (!$this->valueResolver->isValue($firstExpr, 1)) {
            return \false;
        }
        return $this->valueResolver->isValue($seconcExpr, -1);
    }
    /**
     * @return BattleshipCompareOrder::*|null
     */
    private function evaluateTernaryAsc(Ternary $ternary) : ?string
    {
        if (!$ternary->if instanceof Expr) {
            return null;
        }
        if ($this->isValueOneAndMinusOne($ternary->if, $ternary->else)) {
            return BattleshipCompareOrder::ASC;
        }
        if ($this->isValueOneAndMinusOne($ternary->else, $ternary->if)) {
            return BattleshipCompareOrder::DESC;
        }
        return null;
    }
    /**
     * @return BattleshipCompareOrder::*|null
     */
    private function evaluateTernaryDesc(Ternary $ternary) : ?string
    {
        if (!$ternary->if instanceof Expr) {
            return null;
        }
        if ($this->isValueOneAndMinusOne($ternary->if, $ternary->else)) {
            return BattleshipCompareOrder::DESC;
        }
        if ($this->isValueOneAndMinusOne($ternary->else, $ternary->if)) {
            return BattleshipCompareOrder::ASC;
        }
        return null;
    }
}
