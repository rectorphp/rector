<?php

declare (strict_types=1);
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
    public function resolve(\PhpParser\Node\Stmt\Switch_ $switch) : array
    {
        $condAndExpr = [];
        foreach ($switch->cases as $case) {
            if (!isset($case->stmts[0])) {
                return [];
            }
            $expr = $case->stmts[0];
            if ($expr instanceof \PhpParser\Node\Stmt\Expression) {
                $expr = $expr->expr;
            }
            if ($expr instanceof \PhpParser\Node\Stmt\Return_) {
                $returnedExpr = $expr->expr;
                if (!$returnedExpr instanceof \PhpParser\Node\Expr) {
                    return [];
                }
                $condAndExpr[] = new \Rector\Php80\ValueObject\CondAndExpr($case->cond, $returnedExpr, \Rector\Php80\ValueObject\CondAndExpr::TYPE_RETURN);
            } elseif ($expr instanceof \PhpParser\Node\Expr\Assign) {
                $condAndExpr[] = new \Rector\Php80\ValueObject\CondAndExpr($case->cond, $expr, \Rector\Php80\ValueObject\CondAndExpr::TYPE_ASSIGN);
            } elseif ($expr instanceof \PhpParser\Node\Expr) {
                $condAndExpr[] = new \Rector\Php80\ValueObject\CondAndExpr($case->cond, $expr, \Rector\Php80\ValueObject\CondAndExpr::TYPE_NORMAL);
            } else {
                return [];
            }
        }
        return $condAndExpr;
    }
}
