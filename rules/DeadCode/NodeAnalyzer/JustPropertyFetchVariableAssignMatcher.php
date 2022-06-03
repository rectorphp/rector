<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeAnalyzer;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\DeadCode\ValueObject\VariableAndPropertyFetchAssign;
final class JustPropertyFetchVariableAssignMatcher
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(\Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator)
    {
        $this->nodeComparator = $nodeComparator;
    }
    public function match(\Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface $stmtsAware) : ?\Rector\DeadCode\ValueObject\VariableAndPropertyFetchAssign
    {
        $stmts = (array) $stmtsAware->stmts;
        $stmtCount = \count($stmts);
        // must be exactly 3 stmts
        if ($stmtCount !== 3) {
            return null;
        }
        $firstVariableAndPropertyFetchAssign = $this->matchVariableAndPropertyFetchAssign($stmts[0]);
        if (!$firstVariableAndPropertyFetchAssign instanceof \Rector\DeadCode\ValueObject\VariableAndPropertyFetchAssign) {
            return null;
        }
        $thirdVariableAndPropertyFetchAssign = $this->matchRevertedVariableAndPropertyFetchAssign($stmts[2]);
        if (!$thirdVariableAndPropertyFetchAssign instanceof \Rector\DeadCode\ValueObject\VariableAndPropertyFetchAssign) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($firstVariableAndPropertyFetchAssign->getPropertyFetch(), $thirdVariableAndPropertyFetchAssign->getPropertyFetch())) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($firstVariableAndPropertyFetchAssign->getVariable(), $thirdVariableAndPropertyFetchAssign->getVariable())) {
            return null;
        }
        return $firstVariableAndPropertyFetchAssign;
    }
    private function matchVariableAndPropertyFetchAssign(\PhpParser\Node\Stmt $stmt) : ?\Rector\DeadCode\ValueObject\VariableAndPropertyFetchAssign
    {
        if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        if (!$stmt->expr instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        $assign = $stmt->expr;
        if (!$assign->expr instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return null;
        }
        if (!$assign->var instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        return new \Rector\DeadCode\ValueObject\VariableAndPropertyFetchAssign($assign->var, $assign->expr);
    }
    private function matchRevertedVariableAndPropertyFetchAssign(\PhpParser\Node\Stmt $stmt) : ?\Rector\DeadCode\ValueObject\VariableAndPropertyFetchAssign
    {
        if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        if (!$stmt->expr instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        $assign = $stmt->expr;
        if (!$assign->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return null;
        }
        if (!$assign->expr instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        return new \Rector\DeadCode\ValueObject\VariableAndPropertyFetchAssign($assign->expr, $assign->var);
    }
}
