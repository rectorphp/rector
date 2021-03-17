<?php

namespace Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

use Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\Source\MethodWithFuncGetArgs;

class SkipExternalScope
{
    function run()
    {
        MethodWithFuncGetArgs::call(1, 2, 3);
    }
}
