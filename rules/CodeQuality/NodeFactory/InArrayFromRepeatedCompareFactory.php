<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use Rector\CodeQuality\ValueObject\ComparedExprAndValueExpr;
use Rector\NodeAnalyzer\ExprAnalyzer;
use Rector\PhpParser\Comparing\NodeComparator;
use Rector\PhpParser\Node\NodeFactory;
final class InArrayFromRepeatedCompareFactory
{
    /**
     * @readonly
     */
    private NodeComparator $nodeComparator;
    /**
     * @readonly
     */
    private NodeFactory $nodeFactory;
    /**
     * @readonly
     */
    private ExprAnalyzer $exprAnalyzer;
    public function __construct(NodeComparator $nodeComparator, NodeFactory $nodeFactory, ExprAnalyzer $exprAnalyzer)
    {
        $this->nodeComparator = $nodeComparator;
        $this->nodeFactory = $nodeFactory;
        $this->exprAnalyzer = $exprAnalyzer;
    }
    /**
     * Builds the "$value, [...]" args of an in_array() call from a repeated compare chain,
     * once all compared expressions are confirmed equal. Returns null when the chain is too
     * short, the compared expressions differ, or a value expression is not safe to evaluate eagerly.
     *
     * @param ComparedExprAndValueExpr[] $comparedExprAndValueExprs
     * @return Arg[]|null
     */
    public function createInArrayArgs(array $comparedExprAndValueExprs): ?array
    {
        if (count($comparedExprAndValueExprs) < 3) {
            return null;
        }
        $valueExprs = [];
        foreach ($comparedExprAndValueExprs as $comparedExprAndValueExpr) {
            $valueExpr = $comparedExprAndValueExpr->getValueExpr();
            // the array literal evaluates every item up front, while the &&/|| chain stops early,
            // e.g. null !== $a && null !== $a->get() would call get() on null
            if (!$this->isSafeToEvaluateEagerly($valueExpr)) {
                return null;
            }
            $valueExprs[] = $valueExpr;
        }
        /** @var ComparedExprAndValueExpr $firstComparedExprAndValue */
        $firstComparedExprAndValue = array_pop($comparedExprAndValueExprs);
        // all compared expr must be equal
        foreach ($comparedExprAndValueExprs as $comparedExprAndValueExpr) {
            if (!$this->nodeComparator->areNodesEqual($firstComparedExprAndValue->getComparedExpr(), $comparedExprAndValueExpr->getComparedExpr())) {
                return null;
            }
        }
        $array = $this->nodeFactory->createArray($valueExprs);
        return $this->nodeFactory->createArgs([$firstComparedExprAndValue->getComparedExpr(), $array]);
    }
    private function isSafeToEvaluateEagerly(Expr $expr): bool
    {
        // reading a plain variable has no side effect; $$name could evaluate a call
        if ($expr instanceof Variable) {
            return is_string($expr->name);
        }
        return !$this->exprAnalyzer->isDynamicExpr($expr);
    }
}
