<?php

declare(strict_types=1);

namespace Rector\Php80\NodeResolver;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
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
            if (! isset($case->stmts[0])) {
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
            } else {
                return [];
            }
        }

        return $condAndExpr;
    }
}
