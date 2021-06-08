<?php

declare(strict_types=1);

namespace Rector\Php80\NodeResolver;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Throw_;
use Rector\Php80\ValueObject\CondAndExpr;

final class SwitchExprsResolver
{
    /**
     * @return CondAndExpr[]
     */
    public function resolve(Switch_ $switch): array
    {
        $condAndExpr = [];

        foreach ($switch->cases as $case) {
            // must be exactly 1 stmt and break
            if (! $this->isValidCase($case)) {
                return [];
            }

            $expr = $case->stmts[0];
            if ($expr instanceof Expression) {
                $expr = $expr->expr;
            }

            if ($expr instanceof Return_) {
                $returnedExpr = $expr->expr;
                if (! $returnedExpr instanceof Expr) {
                    return [];
                }
                $condAndExpr[] = new CondAndExpr($case->cond, $returnedExpr, CondAndExpr::TYPE_RETURN);
            } elseif ($expr instanceof Assign) {
                $condAndExpr[] = new CondAndExpr($case->cond, $expr, CondAndExpr::TYPE_ASSIGN);
            } elseif ($expr instanceof Expr) {
                $condAndExpr[] = new CondAndExpr($case->cond, $expr, CondAndExpr::TYPE_NORMAL);
            } elseif ($expr instanceof Throw_) {
                $throwExpr = new Expr\Throw_($expr->expr);
                $condAndExpr[] = new CondAndExpr($case->cond, $throwExpr, CondAndExpr::TYPE_THROW);
            } else {
                return [];
            }
        }

        return $condAndExpr;
    }

    private function isValidCase(Case_ $case): bool
    {
        if (count($case->stmts) === 2 && $case->stmts[1] instanceof Break_) {
            return true;
        }

        // default throws stmts
        if (count($case->stmts) !== 1) {
            return false;
        }

        // throws expressoin
        if ($case->stmts[0] instanceof Throw_) {
            return true;
        }

        // default value
        return $case->cond === null;
    }
}
