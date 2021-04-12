<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Comparing;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\If_;

final class ConditionSearcher
{
    public function searchIfAndElseForVariableRedeclaration(Assign $node, If_ $ifNode): bool
    {
        /** @var Variable $varNode */
        $varNode = $node->var;

        // search if for redeclaration of variable
        /** @var Node\Stmt\Expression $statementIf */
        foreach ($ifNode->stmts as $statementIf) {
            if (! $statementIf->expr instanceof Assign) {
                continue;
            }

            /** @var Variable $varIf */
            $varIf = $statementIf->expr->var;
            if ($varNode->name !== $varIf->name) {
                continue;
            }

            $elseNode = $ifNode->else;
            if (! $elseNode instanceof Else_) {
                continue;
            }

            // search else for redeclaration of variable
            return $this->searchElseForVariableRedeclaration($node, $elseNode);
        }

        return false;
    }

    private function searchElseForVariableRedeclaration(Assign $node, Else_ $elseNode): bool
    {
        /** @var Node\Stmt\Expression $statementElse */
        foreach ($elseNode->stmts as $statementElse) {
            if (! $statementElse->expr instanceof Assign) {
                continue;
            }

            /** @var Variable $varElse */
            $varElse = $statementElse->expr->var;
            /** @var Variable $varNode */
            $varNode = $node->var;
            if ($varNode->name !== $varElse->name) {
                continue;
            }

            return true;
        }

        return false;
    }
}
