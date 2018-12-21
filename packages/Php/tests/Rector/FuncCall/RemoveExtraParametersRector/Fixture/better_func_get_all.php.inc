<?php

namespace Rector\Php\Tests\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

use Rector\Php\Tests\Rector\FuncCall\RemoveExtraParametersRector\Source\MethodWithFuncGetArgs;

final class BetterFuncGetAll
{
    public function run()
    {
        $this->performBetter(1, 2);
        MethodWithFuncGetArgs::betterCall(1, 2);
    }

    function performBetter($value)
    {
        $argumemnts = better_func_get_args();
        var_dump($argumemnts);
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

use Rector\Php\Tests\Rector\FuncCall\RemoveExtraParametersRector\Source\MethodWithFuncGetArgs;

final class BetterFuncGetAll
{
    public function run()
    {
        $this->performBetter(1);
        MethodWithFuncGetArgs::betterCall(1);
    }

    function performBetter($value)
    {
        $argumemnts = better_func_get_args();
        var_dump($argumemnts);
    }
}

?>
