<?php

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

use PhpParser\Node\Expr\MethodCall;

class CountOrArgs
{
    public function run(MethodCall $methodCall)
    {
        if (count($methodCall->args)) {
            return true;
        }

        return false;
    }
}

?>
-----
<?php

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

use PhpParser\Node\Expr\MethodCall;

class CountOrArgs
{
    public function run(MethodCall $methodCall)
    {
        if (count((array) $methodCall->args)) {
            return true;
        }

        return false;
    }
}

?>
