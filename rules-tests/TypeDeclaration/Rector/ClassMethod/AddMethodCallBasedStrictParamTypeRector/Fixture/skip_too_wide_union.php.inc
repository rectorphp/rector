<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;

final class SkipTooWideUnion
{
    public function run(MethodCall $methodCall, StaticCall $staticCall, String_ $string, LNumber $number)
    {
        $this->someExpr($methodCall);
        $this->someExpr($staticCall);
        $this->someExpr($string);
        $this->someExpr($number);
    }

    private function someExpr(Expr $expr)
    {
    }
}
