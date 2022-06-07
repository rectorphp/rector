<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Comparing;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
final class ConditionSearcher
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
    public function __construct(BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
    }
    public function hasIfAndElseForVariableRedeclaration(Assign $assign, If_ $if) : bool
    {
        if (!$if->else instanceof Else_) {
            return \false;
        }
        $ifElse = $if->else;
        /** @var Variable $varNode */
        $varNode = $assign->var;
        if (!$this->hasVariableRedeclaration($varNode, $if->stmts)) {
            return \false;
        }
        foreach ($if->elseifs as $elseifNode) {
            if (!$this->hasVariableRedeclaration($varNode, $elseifNode->stmts)) {
                return \false;
            }
        }
        $isInCond = (bool) $this->betterNodeFinder->findFirst($if->cond, function (Node $subNode) use($varNode) : bool {
            return $this->nodeComparator->areNodesEqual($varNode, $subNode);
        });
        if ($isInCond) {
            return \false;
        }
        return $this->hasVariableRedeclaration($varNode, $ifElse->stmts);
    }
    /**
     * @param Stmt[] $stmts
     */
    private function hasVariableRedeclaration(Variable $variable, array $stmts) : bool
    {
        foreach ($stmts as $stmt) {
            if ($this->hasVariableUsedInExpression($variable, $stmt)) {
                return \false;
            }
            if ($this->hasVariableDeclaration($variable, $stmt)) {
                return \true;
            }
        }
        return \false;
    }
    private function hasVariableUsedInExpression(Variable $variable, Stmt $stmt) : bool
    {
        if ($stmt instanceof Expression) {
            $node = $stmt->expr instanceof Assign ? $stmt->expr->expr : $stmt->expr;
        } else {
            $node = $stmt;
        }
        return (bool) $this->betterNodeFinder->findFirst($node, function (Node $subNode) use($variable) : bool {
            return $this->nodeComparator->areNodesEqual($variable, $subNode);
        });
    }
    private function hasVariableDeclaration(Variable $variable, Stmt $stmt) : bool
    {
        if (!$stmt instanceof Expression) {
            return \false;
        }
        if (!$stmt->expr instanceof Assign) {
            return \false;
        }
        $assign = $stmt->expr;
        if (!$assign->var instanceof Variable) {
            return \false;
        }
        $assignedVariable = $assign->var;
        return $variable->name === $assignedVariable->name;
    }
}
