<?php

declare (strict_types=1);
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
    public function resolve(\PhpParser\Node\Stmt\Switch_ $switch) : array
    {
        $condAndExpr = [];
        foreach ($switch->cases as $case) {
            // must be exactly 1 stmt and break
            if (!$this->isValidCase($case)) {
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
            } elseif ($expr instanceof \PhpParser\Node\Stmt\Throw_) {
                $throwExpr = new \PhpParser\Node\Expr\Throw_($expr->expr);
                $condAndExpr[] = new \Rector\Php80\ValueObject\CondAndExpr($case->cond, $throwExpr, \Rector\Php80\ValueObject\CondAndExpr::TYPE_THROW);
            } else {
                return [];
            }
        }
        return $condAndExpr;
    }
    private function isValidCase(\PhpParser\Node\Stmt\Case_ $case) : bool
    {
        if (\count($case->stmts) === 2 && $case->stmts[1] instanceof \PhpParser\Node\Stmt\Break_) {
            return \true;
        }
        // default throws stmts
        if (\count($case->stmts) !== 1) {
            return \false;
        }
        // throws expressoin
        if ($case->stmts[0] instanceof \PhpParser\Node\Stmt\Throw_) {
            return \true;
        }
        // instant return
        if ($case->stmts[0] instanceof \PhpParser\Node\Stmt\Return_) {
            return \true;
        }
        // default value
        return $case->cond === null;
    }
}
