<?php

declare (strict_types=1);
namespace Rector\Php80\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Php80\ValueObject\StrStartsWith;
final class StrStartsWithFuncCallFactory
{
    /**
     * @return \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\BooleanNot
     */
    public function createStrStartsWith(StrStartsWith $strStartsWith)
    {
        $args = [new Arg($strStartsWith->getHaystackExpr()), new Arg($strStartsWith->getNeedleExpr())];
        $funcCall = new FuncCall(new Name('str_starts_with'), $args);
        if ($strStartsWith->isPositive()) {
            return $funcCall;
        }
        return new BooleanNot($funcCall);
    }
}
