<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\FixtureUnion;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;

final class NarrowUnion
{
    public function run(MethodCall $methodCall, StaticCall $staticCall, String_ $string)
    {
        $this->someExpr($methodCall);
        $this->someExpr($staticCall);
        $this->someExpr($string);
    }

    private function someExpr(Expr $expr)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\FixtureUnion;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;

final class NarrowUnion
{
    public function run(MethodCall $methodCall, StaticCall $staticCall, String_ $string)
    {
        $this->someExpr($methodCall);
        $this->someExpr($staticCall);
        $this->someExpr($string);
    }

    private function someExpr(\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Scalar\String_ $expr)
    {
    }
}

?>
