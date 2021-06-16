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
use Rector\Php80\ValueObject\MatchKind;

final class SwitchExprsResolver
{
    /**
     * @return CondAndExpr[]
     */
    public function resolve(Switch_ $switch): array
    {
        $condAndExpr = [];
        $collectionEmptyCasesCond = [];

        foreach ($switch->cases as $key => $case) {
            if (! $this->isValidCase($case)) {
                return [];
            }

            if ($case->stmts === [] && $case->cond instanceof Expr) {
                $collectionEmptyCasesCond[$key] = $case->cond;
            }
        }

        foreach ($switch->cases as $key => $case) {
            if ($case->stmts === []) {
                continue;
            }

            $expr = $case->stmts[0];
            if ($expr instanceof Expression) {
                $expr = $expr->expr;
            }

            $condExprs = [];

            if ($case->cond !== null) {
                $emptyCasesCond = [];

                foreach ($collectionEmptyCasesCond as $i => $collectionEmptyCaseCond) {
                    if ($i > $key) {
                        break;
                    }

                    $emptyCasesCond[$i] = $collectionEmptyCaseCond;
                    unset($collectionEmptyCasesCond[$i]);
                }

                $condExprs = $emptyCasesCond;
                $condExprs[] = $case->cond;
            }

            if ($expr instanceof Return_) {
                $returnedExpr = $expr->expr;
                if (! $returnedExpr instanceof Expr) {
                    return [];
                }

                $condAndExpr[] = new CondAndExpr($condExprs, $returnedExpr, MatchKind::RETURN);
            } elseif ($expr instanceof Assign) {
                $condAndExpr[] = new CondAndExpr($condExprs, $expr, MatchKind::ASSIGN);
            } elseif ($expr instanceof Expr) {
                $condAndExpr[] = new CondAndExpr($condExprs, $expr, MatchKind::NORMAL);
            } elseif ($expr instanceof Throw_) {
                $throwExpr = new Expr\Throw_($expr->expr);
                $condAndExpr[] = new CondAndExpr($condExprs, $throwExpr, MatchKind::THROW);
            } else {
                return [];
            }
        }

        return $condAndExpr;
    }

    private function isValidCase(Case_ $case): bool
    {
        // prepend to previous one
        if ($case->stmts === []) {
            return true;
        }

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

        // instant return
        if ($case->stmts[0] instanceof Return_) {
            return true;
        }

        // default value
        return $case->cond === null;
    }
}
