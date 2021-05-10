<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Comparing;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
final class ConditionSearcher
{
    public function searchIfAndElseForVariableRedeclaration(\PhpParser\Node\Expr\Assign $assign, \PhpParser\Node\Stmt\If_ $if) : bool
    {
        /** @var Variable $varNode */
        $varNode = $assign->var;
        // search if for redeclaration of variable
        foreach ($if->stmts as $statementIf) {
            if (!$statementIf instanceof \PhpParser\Node\Stmt\Expression) {
                continue;
            }
            if (!$statementIf->expr instanceof \PhpParser\Node\Expr\Assign) {
                continue;
            }
            $assignVar = $statementIf->expr->var;
            if (!$assignVar instanceof \PhpParser\Node\Expr\Variable) {
                continue;
            }
            if ($varNode->name !== $assignVar->name) {
                continue;
            }
            $elseNode = $if->else;
            if (!$elseNode instanceof \PhpParser\Node\Stmt\Else_) {
                continue;
            }
            // search else for redeclaration of variable
            return $this->searchElseForVariableRedeclaration($assign, $elseNode);
        }
        return \false;
    }
    private function searchElseForVariableRedeclaration(\PhpParser\Node\Expr\Assign $assign, \PhpParser\Node\Stmt\Else_ $else) : bool
    {
        foreach ($else->stmts as $statementElse) {
            if (!$statementElse instanceof \PhpParser\Node\Stmt\Expression) {
                continue;
            }
            if (!$statementElse->expr instanceof \PhpParser\Node\Expr\Assign) {
                continue;
            }
            /** @var Variable $varElse */
            $varElse = $statementElse->expr->var;
            /** @var Variable $varNode */
            $varNode = $assign->var;
            if ($varNode->name !== $varElse->name) {
                continue;
            }
            return \true;
        }
        return \false;
    }
}
