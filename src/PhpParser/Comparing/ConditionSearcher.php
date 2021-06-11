<?php

declare(strict_types=1);

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
    public function __construct(
        private BetterNodeFinder $betterNodeFinder,
        private NodeComparator $nodeComparator
    ) {
    }

    public function searchIfAndElseForVariableRedeclaration(Assign $assign, If_ $if): bool
    {
        $elseNode = $if->else;

        if (! $elseNode instanceof Else_) {
            return false;
        }

        /** @var Variable $varNode */
        $varNode = $assign->var;

        if (! $this->searchForVariableRedeclaration($varNode, $if->stmts)) {
            return false;
        }

        foreach ($if->elseifs as $elseifNode) {
            if (! $this->searchForVariableRedeclaration($varNode, $elseifNode->stmts)) {
                return false;
            }
        }

        if (! $this->searchForVariableRedeclaration($varNode, $elseNode->stmts)) {
            return false;
        }

        return true;
    }

    /**
     * @param Stmt[] $stmts
     */
    private function searchForVariableRedeclaration(Variable $varNode, array $stmts): bool
    {
        foreach ($stmts as $stmt) {
            if ($this->checkIfVariableUsedInExpression($varNode, $stmt)) {
                return false;
            }

            if ($this->checkForVariableRedeclaration($varNode, $stmt)) {
                return true;
            }
        }

        return false;
    }

    private function checkIfVariableUsedInExpression(Variable $varNode, Stmt $stmt): bool
    {
        if ($stmt instanceof Expression) {
            if ($stmt->expr instanceof Assign) {
                $node = $stmt->expr->expr;
            } else {
                $node = $stmt->expr;
            }
        } else {
            $node = $stmt;
        }

        return (bool) $this->betterNodeFinder->findFirst(
            $node,
            fn (Node $subNode): bool => $this->nodeComparator->areNodesEqual($varNode, $subNode)
        );
    }

    private function checkForVariableRedeclaration(Variable $varNode, Stmt $stmt): bool
    {
        if (! $stmt instanceof Expression) {
            return false;
        }

        if (! $stmt->expr instanceof Assign) {
            return false;
        }

        $assignVar = $stmt->expr->var;
        if (! $assignVar instanceof Variable) {
            return false;
        }

        if ($varNode->name !== $assignVar->name) {
            return false;
        }

        return true;
    }
}
