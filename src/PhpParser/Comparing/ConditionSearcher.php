<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Comparing;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Nop;

final class ConditionSearcher
{
    public function searchIfAndElseForVariableRedeclaration(Assign $assign, If_ $if): bool
    {
        /** @var Variable $varNode */
        $varNode = $assign->var;

        // search if for redeclaration of variable
        foreach ($if->stmts as $statementIf) {
            if ($statementIf instanceof Nop) { // Nop stmt doesn't has expr property
                continue;
            }

            if (! $statementIf->expr instanceof Assign) {
                continue;
            }

            $assignVar = $statementIf->expr->var;
            if (! $assignVar instanceof Variable) {
                continue;
            }

            if ($varNode->name !== $assignVar->name) {
                continue;
            }

            $elseNode = $if->else;
            if (! $elseNode instanceof Else_) {
                continue;
            }

            // search else for redeclaration of variable
            return $this->searchElseForVariableRedeclaration($assign, $elseNode);
        }

        return false;
    }

    private function searchElseForVariableRedeclaration(Assign $assign, Else_ $else): bool
    {
        /** @var Expression $statementElse */
        foreach ($else->stmts as $statementElse) {
            if ($statementElse instanceof Nop) { // Nop stmt doesn't has expr property
                continue;
            }

            if (! $statementElse->expr instanceof Assign) {
                continue;
            }

            /** @var Variable $varElse */
            $varElse = $statementElse->expr->var;
            /** @var Variable $varNode */
            $varNode = $assign->var;
            if ($varNode->name !== $varElse->name) {
                continue;
            }

            return true;
        }

        return false;
    }
}
