<?php

namespace Rector\Php\Tests\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

use Rector\Php\Tests\Rector\FuncCall\RemoveExtraParametersRector\Source\MethodWithFuncGetArgs;

final class BetterFuncGetAll
{
    public function run()
    {
        MethodWithFuncGetArgs::betterCall(1, 2);
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
        MethodWithFuncGetArgs::betterCall(1);
    }
}

?>
