<?php

namespace Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

use Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\Source\MethodWithFuncGetArgs;

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

namespace Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

use Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\Source\MethodWithFuncGetArgs;

final class BetterFuncGetAll
{
    public function run()
    {
        MethodWithFuncGetArgs::betterCall(1);
    }
}

?>
