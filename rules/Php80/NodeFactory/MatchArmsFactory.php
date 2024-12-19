<?php

declare (strict_types=1);
namespace Rector\Php80\NodeFactory;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\MatchArm;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
        foreach ($condAndExprs as $key => $condAndExpr) {
            $expr = $condAndExpr->getExpr();
            if ($expr instanceof Assign) {
                $expr = $expr->expr;
            }
            /** @var null|list<Expr> $condExprs */
            $condExprs = $condAndExpr->getCondExprs();
            $matchArms[] = new MatchArm($condExprs, $expr, [AttributeKey::COMMENTS => $condAndExprs[$key]->getComments()]);
        }
        return $matchArms;
    }
}
