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
     * @return \PhpParser\Node\Expr\BooleanNot|\PhpParser\Node\Expr\FuncCall
     */
    public function createStrStartsWith(\Rector\Php80\ValueObject\StrStartsWith $strStartsWith)
    {
        $args = [new \PhpParser\Node\Arg($strStartsWith->getHaystackExpr()), new \PhpParser\Node\Arg($strStartsWith->getNeedleExpr())];
        $funcCall = new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('str_starts_with'), $args);
        if ($strStartsWith->isPositive()) {
            return $funcCall;
        }
        return new \PhpParser\Node\Expr\BooleanNot($funcCall);
    }
}
