<?php

declare (strict_types=1);
namespace Rector\Php80\NodeFactory;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\MatchArm;
use Rector\Php80\ValueObject\CondAndExpr;
final class MatchArmsFactory
{
    /**
     * @param CondAndExpr[] $condAndExprs
     * @return MatchArm[]
     */
    public function createFromCondAndExprs(array $condAndExprs) : array
    {
        $matchArms = [];
        foreach ($condAndExprs as $condAndExpr) {
            $expr = $condAndExpr->getExpr();
            if ($expr instanceof \PhpParser\Node\Expr\Assign) {
                // $this->assignExpr = $expr->var;
                $expr = $expr->expr;
            }
            $condExprs = $condAndExpr->getCondExprs();
            $matchArms[] = new \PhpParser\Node\MatchArm($condExprs, $expr);
        }
        return $matchArms;
    }
}
