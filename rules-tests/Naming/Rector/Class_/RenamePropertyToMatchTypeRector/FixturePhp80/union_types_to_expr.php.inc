<?php

namespace Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\FixturePhp80;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;

final class UnionTypesToExpr
{
    /**
     * @param FuncCall|StaticCall|MethodCall $call
     */
    public function __construct(
        private Expr $call
    ) {
    }

    /**
     * @return FuncCall|StaticCall|MethodCall
     */
    public function getCall(): Expr
    {
        return $this->call;
    }
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\FixturePhp80;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;

final class UnionTypesToExpr
{
    /**
     * @param FuncCall|StaticCall|MethodCall $expr
     */
    public function __construct(
        private Expr $expr
    ) {
    }

    /**
     * @return FuncCall|StaticCall|MethodCall
     */
    public function getCall(): Expr
    {
        return $this->expr;
    }
}

?>
